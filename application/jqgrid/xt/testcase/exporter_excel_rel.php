<?php
require_once('exporter_excel.php');

class xt_testcase_exporter_excel_rel extends exporter_excel{
	public function setOptions($jqgrid_action){
		$titles = array(
			array('index'=>'module', 'width'=>100, 'label'=>'Module', 'cols'=>1),
			array('index'=>'code', 'width'=>100, 'label'=>'Testcase ID', 'cols'=>1),
			array('index'=>'summary', 'width'=>200, 'label'=>'Summary', 'cols'=>1),
			array('index'=>'category', 'width'=>100, 'label'=>'Category', 'cols'=>1, 'hidden'=>true),
			array('index'=>'auto_level', 'width'=>100, 'label'=>'Auto Level', 'cols'=>1),
			array('index'=>'priority', 'width'=>100, 'label'=>'Priority', 'cols'=>1),
			array('index'=>'objective', 'width'=>200, 'label'=>'Objective', 'cols'=>1),
			array('index'=>'precondition', 'width'=>200, 'label'=>'Precondition', 'cols'=>1, 'hidden'=>true),
			array('index'=>'steps', 'width'=>400, 'label'=>'Steps', 'cols'=>1),
			array('index'=>'expected_result', 'width'=>200, 'label'=>'Expected Result', 'cols'=>1),
			array('index'=>'command', 'width'=>100, 'label'=>'Command', 'cols'=>1),
			array('index'=>'resource_link', 'width'=>100, 'label'=>'Resource Link', 'cols'=>1),
		);
		$data = $this->getData(null);
		$this->params['sheets'][0] = array('title'=>'Testcase', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles)), 'data'=>$data);
		// $this->params['sheets'][0]['groups'] = array(array('index'=>'module'));		
	}
	
	protected function getData($table_desc, $searchConditions = array(), $order = array()){
		$db = dbFactory::get($this->params['db']);
		$ids = implode(',', $this->params['id']);
		if($this->params['prj_id'] == -1 || $this->params['prj_id'] == '-1'){
			$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, priority.name as priority,".
				" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
				" FROM testcase_ver ver ".
				" left join (".
					" SELECT testcase_ver.testcase_id, max(ver) as max_ver ".
					" FROM testcase_ver".
					" left join prj_testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id".
					" WHERE testcase_ver.testcase_id in ($ids) and not isnull(prj_testcase_ver.id) and testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
					" group by testcase_id".
				") max_ver on ver.testcase_id=max_ver.testcase_id".
				" left join testcase tc on tc.id=ver.testcase_id ".
				" left join testcase_module module on tc.testcase_module_id=module.id".
				" left join testcase_category category on tc.testcase_category_id=category.id".
				" left join testcase_source source on tc.testcase_source_id=source.id".
				" left join testcase_priority priority on ver.testcase_priority_id=priority.id".
				" left join auto_level on ver.auto_level_id=auto_level.id".
				" WHERE tc.id in ($ids) and ver.ver=max_ver.max_ver and ver.testcase_id=max_ver.testcase_id".
				" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
				" ORDER BY module ASC";
		}
		else{
			$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, priority.name as priority,".
				" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
				" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
				" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
				" left join testcase_module module on tc.testcase_module_id=module.id".
				" left join testcase_category category on tc.testcase_category_id=category.id".
				" left join testcase_source source on tc.testcase_source_id=source.id".
				" left join auto_level on ver.auto_level_id=auto_level.id".
				" left join testcase_priority priority on ver.testcase_priority_id=priority.id".
				" WHERE tc.id in ($ids) and link.prj_id={$this->params['prj_id']}".
				" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
				" ORDER BY module ASC";
		}
// print_r($sql);		
		// $sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, priority.name as priority,".
			// " auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ".
			// "ver.expected_result, ver.command, ver.resource_link ".
			// " FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
			// " left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			// " left join testcase_module module on tc.testcase_module_id=module.id".
			// " left join testcase_category category on tc.testcase_category_id=category.id".
			// " left join testcase_source source on tc.testcase_source_id=source.id".
			// " left join auto_level on ver.auto_level_id=auto_level.id".
			// " left join testcase_priority priority on ver.testcase_priority_id=priority.id".
			// " WHERE tc.id in ($ids) and link.prj_id={$this->params['prj_id']}".
			// " and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
			// " ORDER BY module ASC";
		$res = $db->query($sql);
		$rows = array();
		while($row = $res->fetch()){
			$rows[] = $row; 
		}
		return $rows;
	}
};

?>
