<?php
require_once(APPLICATION_PATH.'/jqgrid/action_cloneit.php');

class xt_zzvw_prj_action_cloneit extends action_cloneit{
	protected function _saveOne($db, $table, $pair){
		$prj_id = parent::_saveOne($db, 'prj', $pair);
		$sql = "INSERT INTO prj_testcase_ver (prj_id, testcase_id, testcase_ver_id, note, owner_id, testcase_priority_id, edit_status_id, auto_level_id) ".
			" SELECT $prj_id, testcase_id, testcase_ver_id, note, owner_id, testcase_priority_id, edit_status_id, auto_level_id".
			" FROM prj_testcase_ver WHERE prj_id={$this->orig_id} AND (edit_status_id=1 OR edit_status_id=2)";
//print_r($sql);		
		$this->db->query($sql);
		return $prj_id;
	}
}
?>