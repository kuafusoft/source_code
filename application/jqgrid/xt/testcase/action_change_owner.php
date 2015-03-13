<?php
require_once(APPLICATION_PATH.'/jqgrid/action_change_owner.php');

class xt_testcase_action_change_owner extends action_change_owner{
	protected function handlePost(){
		$ids = implode(',', json_decode($this->params['id']));
		$edit_status = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
		$sql = "UPDATE testcase_ver LEFT JOIN prj_testcase_ver on prj_testcase_ver.testcase_ver_id=testcase_ver.id".
			" set testcase_ver.owner_id={$this->params['select_item']} ".
			" WHERE prj_testcase_ver.prj_id={$this->params['prj_id']} and prj_testcase_ver.testcase_id in ($ids)".
			" AND testcase_ver.edit_status_id IN ($edit_status)";
		$this->tool->query($sql);
	}
}

?>