<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
view: table
node: table_node
tree: table_tree
*/
class action_tree_save extends action_save{
	protected $node_table = '', $tree_table = '';
	
	protected function init(&$controller){
		parent::init($controller);
		$this->setHelpTable();
	}
	
	protected function setHelpTable($helpTable = array()){
		$pre = $this->get('table');
		if (empty($helpTable['pre']))
			$pre = $this->get('table');
		if (stripos($pre, 'zzvw_') === 0){
			$pre = substr($pre, 5);
		}
		if (empty($helpTable['treeTable']))
			$helpTable['treeTable'] = $pre.'_tree';
		if (empty($helpTable['nodeTable']))
			$helpTable['nodeTable'] = $pre.'_node';
		$this->node_table = $helpTable['nodeTable'];
		$this->tree_table = $helpTable['treeTable'];
	}
	
	protected function save($db, $table, $params){
		$node = $this->prepare($db, $this->node_table, $params);
		$node_id = parent::_saveOne($db, $this->node_table, $node);
		$params['node_id'] = $node_id;
		$tree = $this->prepare($db, $this->tree_table, $params);
		if (!empty($params['pid']))
			$tree['ps'] = $params['pid'].'-'.$node_id;
		else
			$tree['ps'] = $node_id;
//print_r($ver);
		$tree_id = parent::_saveOne($db, $this->tree_table, $tree);
		return $tree_id;
    }
}
?>