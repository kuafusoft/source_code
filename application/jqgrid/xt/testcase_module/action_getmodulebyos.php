<?php
require_once('action_jqgrid.php');
class xt_testcase_module_action_getmodulebyos extends action_jqgrid{
	protected function handlePost(){
		$sql = "SELECT DISTINCT testcase_module.id, testcase_module.name ".
			" FROM testcase_module LEFT JOIN testcase_module_testcase_type on testcase_module.id=testcase_module_testcase_type.testcase_module_id".
			" LEFT JOIN os_testcase_type ON os_testcase_type.testcase_type_id=testcase_module_testcase_type.testcase_type_id";
		$where = 'testcase_module.name IS NOT NULL';
		if (!empty($this->params['os_id']))
			$where .= " AND os_testcase_type.os_id=".$this->params['os_id'];
		$sql .= " WHERE $where ORDER BY name ASC";
// print_r($sql)		;
		$res = $this->tool->query($sql);
		return json_encode($res->fetchAll());
	}
}
?>