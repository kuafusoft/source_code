<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');

class xt_os_action_save extends action_save{
	protected function newRecord($db, $table, $pair){
		$os_id = parent::newRecord($db, $table, $pair);
		foreach($this->params['testcase_type_ids'] as $testcase_type_id){
			$this->db->insert('os_testcase_type', array('os_id'=>$os_id, 'testcase_type_id'=>$testcase_type_id));
		}
		return $os_id;
	}
	
	protected function updateRecord($db, $table, $pair){
		$os_id = parent::updateRecord($db, $table, $pair);
		$this->db->delete('os_testcase_type', "os_id=$os_id");
		foreach($this->params['testcase_type_ids'] as $testcase_type_id){
			$this->db->insert('os_testcase_type', array('os_id'=>$os_id, 'testcase_type_id'=>$testcase_type_id));
		}
		return $os_id;
	}
}
?>