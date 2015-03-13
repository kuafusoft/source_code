<?php
/*
树形结构的组织方式：
主要是Adjacent List Model，但增加了一个ps字段，用来存放一个包含父子关系的编码串，便于父子查找
一个树形结构被拆解成两张表:xx_tree和xx_node，同时生成一个View xx.
在xx_tree表里包含：id, pid, ps, node_id，主要用来描述树形结构，其中pid可能是其他名称，如report_to_id
在xx_node表里包含具体的字段
*/
class kf_tree{
    private $db, $view, $tree_table, $node_table, $pid;
    public function __construct($db, $view, $pid = "pid"){
        $this->db = $db;
        $this->view = $view;
        $this->tree_table = $view."_tree";
        $this->node_table = $view."_node";
        $this->pid = $pid;
    }
    
    public function addRoot($valuePair){
//print_r($valuePair);    
        $this->db->insert($this->node_table, $valuePair);
        $node_id = $this->db->lastInsertId();
//print_r($node_id);        
        $this->db->insert($this->tree_table, array('node_id'=>$node_id, $this->pid=>0, "ps"=>$node_id));
        return $this->db->lastInsertId();
    }
    
    public function addChild($pid, $valuePair){
        if (empty($pid))
            return $this->addRoot($valuePair);
        $this->db->insert($this->node_table, $valuePair);
        $node_id = $this->db->lastInsertId();
        $p_ps = $this->getPs($pid);
        $tree_info = array("node_id"=>$node_id, $this->pid=>$pid, "ps"=>$p_ps."-".$node_id);
//print_r($tree_info);
        $this->db->insert($this->tree_table, $tree_info);
        return $this->db->lastInsertId();
    }
    
    public function parents($nodeId, $limit = 1){ //if $limit = 0/-1, then return all the parents
        $ps = $this->getPs($nodeId);
        $parent_ids = explode('-', $ps);
        array_pop($parent_ids);
        if (empty($parent_ids))
            return null;
        $sql = "select * from {$this->view} WHERE node_id in (".implode(',', $parent_ids).")";
        if ($limit > 0)
            $sql .= " LIMIT 0, $limit";
        $res = $this->db->query($sql);
        return $res->fetchAll();        
    }

    public function children($nodeId, $level = 0, $limit = 1){ // if $level = 0, then query the all children, if $limit = 0/-1, then return all the children
        $ps = $this->getPs($nodeId);
        $like = "$ps-";
        if ($level > 0){
            $like = "$ps(-[0-9]+){1,$level}$";
        }
        $sql = "SELECT * FROM {$this->view} WHERE ps REGEXP  '$like'";
        if ($limit > 0)
            $sql .= " LIMIT 0, $limit";
//print_r($sql);
        $res = $this->db->query($sql);
        return $res->fetchAll();
    }

    public function change($nodeId, $valuePair, $includeSub = false, $includeParents = false){
        $data = $this->getData($nodeId);
        $ps = $data['ps'];
//        $this->db->beginTransaction();
        try{
            $this->db->update($this->node_table, $valuePair, "id=".$data['node_id']);
            if ($includeSub){
                $this->updateChildren($ps, $valuePair);
            }
            if ($includeParents){
                $this->updateParents($ps, $valuePair);
            }
//            $this->db->commit();
        }catch(Exception $e){
//            $this->db->rollback();
            die('Caught exception: '.$e->getMessage());
        }
        return $nodeId;
    }
    
    protected function updateChildren($ps, $valuePair){
        $this->db->update($this->node_table, $valuePair, "ps like '$ps-%'");
    }
    
    protected function updateParents($ps, $valuePair){
        $parents = explode('-', $ps);
        array_pop($parents);
        $this->db->update($this->node_table, $valuePair, "id IN (".implode(',', $parents).")");
    }
    
    public function removeNode($nodeIds){// remove the nodes and all their subnodes
        if (is_array($nodeIds))
            $nodeIds = implode(',', $nodeIds);
        $this->db->beginTransaction();
        $res = $this->db->query("SELECT * FROM {$this->view} WHERE id IN ($nodeIds)");
        try{
            while($row = $res->fetch()){
                $del = "DELETE node, tree FROM {$this->tree_table} tree left join  {$this->node_table} node ON tree.node_id=node.id WHERE tree.id={$row['id']} OR tree.ps like '{$row['ps']}-%'";
                $this->db->query($del);
            }
            $this->db->commit();
        }catch(Exception $e){
            $this->db->rollback();
            die('Caught exception: '.$e->getMessage());
        }
    }
    
    public function moveNode($nodeId, $pid = 0){
        $vp = array($this->pid=>$pid);
        $data = $this->getData($nodeId);
        $ps = $data['ps'];
        $p_data = $this->getData($data['pid']);
        $p_ps = $p_data['ps'];
        if (!empty($pid)){
            $newNode = $this->getData($pid);
            $vp['ps'] = $newNode['ps'].'-'.$data['node_id'];
        }
        $this->db->beginTransaction();
        try{
            $this->db->update($this->tree_table, $vp, "id=$nodeId");
            // update the subnode's ps
            if (!empty($pid)){
                $this->syncPs($ps, $vp['ps']);
            }
            $this->db->commit();
        }catch(Exception $e){
            $this->db->rollback();
            die('Caught exception: '.$e->getMessage());
        }
    }
    
    /* 接管某个Node的subnodes*/
    public function takeoverSubs($nodeId, $tookNodeId){
        $oldPs = $this->getPs($tookNodeId);
        $newPs = $this->getPs($nodeId);
        $this->db->update($this->tree_table, array($this->pid=>$nodeId), $this->pid.'='.$tookNodeId);
        $this->syncPs($oldPs, $newPs);        
    }
    
    protected function syncPs($oldPrefix, $newPrefix){
        $pattern = "/^($oldPrefix)(.*)$/";
        $replace = $newPrefix.'$2';
        $sql = "SELECT * FROM {$this->tree_table} WHERE ps like '$oldPrefix-%'";
        $res = $this->db->query($sql);
        while($row = $res->fetch()){
            $subPs = $row['ps'];
            $newPs = preg_replace($pattern, $replace, $row['ps']);
//print_r("subPs = $subPs, newPs = $newPs\n");
            $this->db->update($this->tree_table, array('ps'=>$newPs), 'id='.$row['id']);
        }
    }
    
    public function getData($nodeId){
//print_r("getData, nodeId = $nodeId \n");
        $sql = "SELECT * FROM {$this->view} WHERE id=$nodeId";
        $result = $this->db->query($sql);
        return $result->fetch();
    }
    
    protected function getPs($nodeId){
        $data = $this->getData($nodeId);
        return $data['ps'];
    }
}

function test(){
require_once 'Zend/Db.php';

$params = array ('host'     => '127.0.0.1',
                 'username' => 'root',
                 'password' => 'dbadmin',
                 'dbname'   => 'program');

$db = Zend_Db::factory('PDO_MYSQL', $params);

$tree = new kf_tree($db, 'org');
$rootId = $tree->addRoot(array('name'=>'GM', 'description'=>'Root'));
$subId1 = $tree->addChild($rootId, array('name'=>'MAD', 'description'=>"Level 1"));
$subId2 = $tree->addChild($rootId, array('name'=>'MAD1', 'description'=>"Level 1"));
$subId3 = $tree->addChild($subId1, array('name'=>'MAD Shanghai Branch', 'description'=>"Level 1-1"));
$subId4 = $tree->addChild($subId3, array('name'=>'MAD Shanghai Branch', 'description'=>"Level 1-1-1"));
$subId5 = $tree->addChild($subId4, array('name'=>'MAD Shanghai Branch', 'description'=>"Level 1-1-1-1"));

$tree->change($subId1, array('description'=>"Multimedia Application Division"));
print_r($tree->getData($subId3));
$tree->moveNode($subId3, $subId2);
print_r($tree->getData($subId3));

$children = $tree->children($rootId, 0, 0);
$parents = $tree->parents($subId5, 0);
print_r($children);
print_r($parents);

$tree->takeoverSubs($subId1, $subId2);
//$tree->removeNode($rootId);
}

//test();
?>
