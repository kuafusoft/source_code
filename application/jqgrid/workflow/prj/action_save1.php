<?php
require_once(APPLICATION_PATH.'/jqgrid/action_tree_save.php');
class workflow_prj_action_save extends action_tree_save{
	protected function save($db, $table, $params){
		$node_id = parent::save($db, $table, $params);
		if ($node_id > 0){
			$link_field = $this->get('table').'_id';
			$type_table = $this->get('table').'_type';
			$prj_type_id = isset($params['prj_type_id']) ? $params['prj_type_id'] : 1;
			$type_table_id = isset($params[$type_table.'_id']) ? $params[$type_table.'_id'] : 0;
			$res = $this->db->query("select * from $type_table WHERE id=".$type_table_id);
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
		}
		return $node_id;
	}
}
?>