<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');

class xt_testcase_type_action_save extends action_save{
	protected function newRecord($db, $table, $pair){
		$type_id = parent::newRecord($db, $table, $pair);
// print_r($pair)		;
		foreach($this->params['os_ids'] as $os_id){
			$this->db->insert('os_testcase_type', array('os_id'=>$os_id, 'testcase_type_id'=>$type_id));
		}
		return $type_id;
	}
	
	protected function updateRecord($db, $table, $pair){
		$type_id = parent::updateRecord($db, $table, $pair);
		$this->db->delete('os_testcase_type', "testcase_type_id=$type_id");
		foreach($this->params['os_ids'] as $os_id){
			$this->db->insert('os_testcase_type', array('os_id'=>$os_id, 'testcase_type_id'=>$type_id));
		}
		return $type_id;
	}
}
?>