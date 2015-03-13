<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');
class xt_testcase_testpoint_action_list extends action_list{
	protected function getUnknownInfoForRow($row, $field){
// print_r($field);
		$res = $this->tool->query("SELECT COUNT(*) as cc FROM xt.testcase WHERE testcase_testpoint_id={$row['id']}");
		$cc = $res->fetch();
		$row['cases'] = $cc['cc'];
		return $row;
	}
}
?>