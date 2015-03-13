<?php
class wz{//物资
    protected $wz_fl = 0;
    protected $model = null;
    protected $db = null;
    public function __construct($model){
        $this->model = $model;
        $this->db = $model->getDbAdapter();
    }
    
    public function _saveOne($pair){
        // get the data of wz table
        $fields = array('wz_id'=>'id', 'name', 'wz_fl_id', 'count_unit', 'default_price', 'warning_level', 'warning_direct_id', 'ck_id', 'remained', 'isactive', 'pic', 'note');
        $vs = $this->filterValues($pair, $fields);
        if (isset($vs['id'])){
            $id = $vs['id'];
            $this->db->update('wz', $vs, "id=".$vs['id']);
        }
        else{
            $this->db->insert('wz', $vs);
            $id = $this->db->lastInsertId();
        }
        return $id;
    }
    
    public function del($params){
        
    }
    //增加合作伙伴
    // 1. 如果该伙伴不存在，则hzhb增加一条记录
    // 2. 如果ht表里没有对应关系，则ht增加一条记录
    public function zjhzhb(){
        $params = $this->model->getParams();
        if($this->model->controller->getRequest()->isPost()){
            //check if the hzhb-wz map existed in ht
            $res = $this->db->query("SELECT * FROM ht WHERE hzhb_id=:hzhb_id AND wz_id=:wz_id AND isactive=1 AND ht_state_id!=5", $params);
            if (!$res->count()){
                $this->db->insert('ht', $params);
            }
        }
        else{
        
        }
    }
    
    /* 采购
    1. 在ht_zx表里增加一条采购记录
    2. 更新ht表相应数据
    3. 更新应付款数据
    */
    public function cg(){
        $params = $this->model->getParams();
        if ($this->model->controller->getRequest()->isPost()){
            $params['ht_zx_id'] = 1; // 下单
            $this->db->insert('ht_zx', $params);
            /*
            一般情况下，可能下单和入库同时完成，还涉及到运输、装卸和支付
            */
            if (!empty($params['wz']['pay'])){
            
            }
            
            if (!empty($params['yunshu']['pay'])){
                
            }
        }
        else{
            // 物资信息、合作伙伴信息、运输单位信息、装卸单位信息、支付帐户信息
            $result = $this->db->query("SELECT * FROM vw_ht WHERE wz_id=:wz_id ORDER BY hzhb ASC", $params);
            $params['ht'] = $result->fetchAll();
            $result = $this->db->query("SELECT * FROM zjzh WHERE 1");
            $params['zjzh'] = $result->fetchAll();
            $result = $this->db->query("select * from vw_ht WHERE wz='运输'");
            $params['yunshu'] = $result->fetchAll();
            $result = $this->db->query("select * from vw_ht WHERE wz='装卸'");
            $params['zhuangxie'] = $result->fetchAll();
            $params['from'] = 'wz';
            $this->renderView('wz_cg.php', $params);
        }
    }

    //发货
    public function fh(){
    
    }
    
    // 下单
    public function xd(){
        
    }
    
    // 入库
    public function rk(){
    
    }
    
    // 出库
    public function ck(){
    
    }
    
    protected function filterValues($pair, $fields){
        $ret = array();
        foreach($fields as $k=>$f){
            if (is_int($k))
                $k = $f;
            if (isset($pair[$k])){
                $ret[$f] = $pair[$k];
            }
        }
        return $ret;
    }
}

class yl extends wz{//原料
    /* 采购
    1. 在ht_zx表里增加一条采购记录
    2. 更新ht表相应数据
    3. 更新应付款数据
    */
    public function cg(){
        $params = $this->model->getParams();
        if ($this->model->controller->getRequest()->isPost()){
            $params['ht_zx_id'] = 1; // 下单
            $this->db->insert('ht_zx', $params);
            /*
            一般情况下，可能下单和入库同时完成，还涉及到运输、装卸和支付
            */
        }
        else{
            // 物资信息、合作伙伴信息、运输单位信息、装卸单位信息、支付帐户信息
            $result = $this->db->query("SELECT * FROM vw_ht WHERE wz_id=:wz_id ORDER BY hzhb ASC", $params);
            $params['ht'] = $result->fetchAll();
            $result = $this->db->query("SELECT * FROM zjzh WHERE 1");
            $params['zjzh'] = $result->fetchAll();
            $result = $this->db->query("select * from vw_ht WHERE wz='运输'");
            $params['yunshu'] = $result->fetchAll();
            $result = $this->db->query("select * from vw_ht WHERE wz='装卸'");
            $params['zhuangxie'] = $result->fetchAll();
            $params['from'] = 'wz';
            $this->renderView('wz_cg.php', $params);
        }
    }
    
    public function fh(){
    
    }
    
    public function _saveOne($pair){
        $wz_id = $parent::_saveOne($pair);
        $vs = array('midu'=>$pair['midu']);
        $id = 0;
        if (isset($pair['id'])){
            $this->db->update('wz_yl', $vs, "id=".$pair['id']);
            $id = $pair['id'];
        }
        else{
            $this->db->insert('wz_yl', $vs);
            $id = $this->db->lastInsertId();
        }
        return $id;
    }
}

class sb extends wz{//设备
    public function wh(){
    
    }
    
    public function _saveOne($pair){
        $wz_id = $parent::_saveOne($pair);
        $fields = array('id', 'mj_id'=>'wz_sb_id', 'tj', 'bmj', 'zuhe', 'ganghao', 'meng_mix', 'meng_max');
        $vs = $this->filterValues($pair, $fields);
        $vs['wz_id'] = $wz_id;
        $id = 0;
        if (isset($pair['id'])){
            $this->db->update('wz_sb', $vs, "id=".$pair['id']);
            $id = $pair['id'];
        }
        else{
            $this->db->insert('wz_sb', $vs);
            $id = $this->db->lastInsertId();
        }
        return $id;
    }
}

class cp extends wz{//产品
    // 验收入库
    public function ysrk(){
    
    }
    
    // 登记生产情况
    public function djsc(){
    
    }
}

?>
