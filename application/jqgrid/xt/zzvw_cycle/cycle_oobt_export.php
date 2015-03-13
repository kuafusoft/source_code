<?php
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_contrast_export.php');
//specially for Ackey

class cycle_oobt_export extends cycle_contrast_export{

	private $data = array();
	private $statisticsHeader = array();
	private $cycleInfo = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
	}

	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$title1 = array();
		$even = true;
		$title1 = array(
			array('label'=>'Test Case Title', 'width'=>400, 'index'=>'testcase_title'),
			array('label'=>'Module', 'width'=>200, 'index'=>'module'),
			array('label'=>'Compiler', 'width'=>200, 'index'=>'compiler'),
			array('label'=>'Rel', 'width'=>200, 'index'=>'rel'),
			array('label'=>'Build Target', 'width'=>200, 'index'=>'build_target'),
			array('label'=>'Precondition', 'width'=>200, 'index'=>'precondition'),
			array('label'=>'Steps', 'width'=>200, 'index'=>'steps'),
			array('label'=>'Expected Result', 'width'=>200, 'index'=>'expected_res'),
			array('label'=>'Result', 'width'=>300, 'index'=>'result'),
			array('label'=>'Comment', 'width'=>500, 'index'=>'comment'),
			array('label'=>'CRID', 'width'=>500, 'index'=>'defect_ids')
		);
		$element = json_decode($this->params['element']);
		$sql = "SELECT * FROM cycle WHERE id =".$element[$worksheetIndex];
		$res = $this->params['db']->query($sql);
		if($row = $res->fetch()){
			$this->writeCell($worksheetIndex, 1, 0, $row['name'], 'summary');
		}
		$this->mergeCells($worksheetIndex, 1, 0, 2, count($title1)-1);
		$this->setColumnHeader(array('rows'=>array($title1)), $worksheetIndex, 3, 0);
		base_report::_writeHeader($worksheetIndex);
	}
    protected function _report($res, $worksheetIndex){
    	switch($worksheetIndex){
			default:
				return base_report::_report($res, $worksheetIndex);
				break;
		}
	}

	protected function getData($worksheetIndex){//worksheetindex=1时没什么问题
		$cr = array();
		$result = array();
		$element = json_decode($this->params['element']);
		$sql = "SELECT detail.id, detail.finish_time, testcase_module.id as module_id, testcase_module.name as module, detail.testcase_id as tc_id,".
			" testcase.id as testcase_id, testcase.code as testcase_title, result_type.name as result_type, detail.result_type_id as result_type_id,".
			" detail.comment as comment, detail.defect_ids as defect_ids, cycle.id as cycle_id, cycle.name as cycle, compiler.name as compiler,".
			" build_target.name as build_target, rel.name as rel, ver.precondition as precondition, ver.steps as steps, ver.expected_result as expected_res".
			" FROM cycle_detail detail LEFT JOIN testcase ON detail.testcase_id=testcase.id".
			" LEFT JOIN testcase_module ON testcase_module.id=testcase.testcase_module_id".
			" LEFT JOIN result_type ON result_type.id=detail.result_type_id".
			" LEFT JOIN cycle ON cycle.id=detail.cycle_id".
			" LEFT JOIN compiler ON cycle.compiler_id=compiler.id".
			" LEFT JOIN rel ON cycle.rel_id=rel.id".
			" LEFT JOIN build_target ON cycle.build_target_id=build_target.id".
			" LEFT JOIN testcase_ver ver ON detail.testcase_ver_id=ver.id".
			" WHERE detail.cycle_id=".$element[$worksheetIndex].
			" ORDER BY testcase.testcase_module_id ASC, detail.testcase_id ASC";
		$res = $this->params['db']->query($sql);

		while($row = $res->fetch()){
			$this->data[$worksheetIndex][$row['id']]['module'] = $row['module'];
			$this->data[$worksheetIndex][$row['id']]['testcase_id'] = $row['testcase_id'];
			$this->data[$worksheetIndex][$row['id']]['testcase_title'] = $row['testcase_title'];
			$this->data[$worksheetIndex][$row['id']]['compiler'] = $row['compiler'];
			$this->data[$worksheetIndex][$row['id']]['rel'] = $row['rel'];
			$this->data[$worksheetIndex][$row['id']]['build_target'] = $row['build_target'];
			$this->data[$worksheetIndex][$row['id']]['precondition'] = $row['precondition'];
			$this->data[$worksheetIndex][$row['id']]['steps'] = $row['steps'];
			$this->data[$worksheetIndex][$row['id']]['expected_res'] = $row['expected_res'];
		}	
		if(isset($this->data[$worksheetIndex]))
			return $this->data[$worksheetIndex];
	}
}
?>
