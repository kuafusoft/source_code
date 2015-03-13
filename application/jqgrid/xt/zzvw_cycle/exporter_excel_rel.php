<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/exporter_excel_rel.php');

class xt_zzvw_cycle_exporter_excel_rel extends xt_testcase_exporter_excel_rel{
	protected function getData($table_desc, $searchConditions = array(), $order = array()){
		$db = dbFactory::get($this->params['db']);
		$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, ".
			" auto_level.name as auto_level, testcase_priority.name as priority, ver.manual_run_minutes, ver.auto_run_minutes, ".
			" ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
			" FROM cycle_detail left join testcase_ver ver on cycle_detail.testcase_ver_id=ver.id".
			" left join testcase tc on tc.id=ver.testcase_id ".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" left join testcase_category category on tc.testcase_category_id=category.id".
			" left join testcase_source source on tc.testcase_source_id=source.id".
			" left join auto_level on ver.auto_level_id=auto_level.id".
			" left join testcase_priority on ver.testcase_priority_id=testcase_priority.id".
			" WHERE cycle_detail.cycle_id in ({$this->params['id']})".
			" ORDER BY module ASC";
		$res = $db->query($sql);
		$rows = array();
		while($row = $res->fetch()){
			$rows[] = $row; 
		}
		return $rows;
	}
};

?>
