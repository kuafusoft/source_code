<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
view: table
node: table_node
tree: table_tree
*/
class action_node_detail_save extends action_save{
	protected function save($db, $table, $params){
//print_r($params);
		$link_field = $this->get('table').'_id';
		$type_table = $this->get('table').'_type';
		$node = $this->prepare($db, $this->get('real_table'), $params);
		$node_id = parent::_saveOne($db, $this->get('real_table'), $node);
		$res = $this->db->query("select * from $type_table WHERE id=".$params[$type_table.'_id']);
		$row = $res->fetch();
		$detail_table = trim($row['table_name']);
		if (!empty($detail_table)){
			$params[$link_field] = $node_id;
			$orig_id = $params['id'];
			unset($params['id']);
			$detail = $this->prepare($db, $detail_table, $params);
			if(!empty($orig_id)){
				$this->db->update($detail_table, $detail, "$link_field=$node_id");
			}
			else{
				$this->db->insert($detail_table, $detail);
			}
		}
		return $node_id;
   }
}
?>