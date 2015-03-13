<?php
/*
从一个Codec的Excel文件中导入数据，包括Case数据、Stream数据、Trick数据和测试结果数据
*/

require_once('excel_parser.php');

class testcase_parser extends excel_parser{
	protected $columnMap = array();
	protected $testcase_type = 'Linux BSP';
	public function __construct($config_file){
		require_once($config_file);
		$this->columnMap = $columnMap;
		$sheets = array_keys($this->columnMap);
		parent::__construct($fileName, $sheets);
		if (!empty($testcase_type))
			$this->testcase_type = $testcase_type;
	}
	
	public function default_analyze_sheet($sheet, $title){
		$columnMap = $this->columnMap[$title];
		$highestRow = $sheet->getHighestRow(); // e.g. 10
		$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
//print_r("title = $title\n");		
		for($row = $columnMap['start_row']; $row <= $highestRow; $row ++){
			if (!empty($columnMap['case']))
				$this->handleNormalCase($sheet, $row, $columnMap, $title);
			if (!empty($columnMap['test_result']))
				$this->handleNormalTestResult($sheet, $row, $columnMap, $title);
		}
//print_r($this->parse_result);		
	}

	protected function handleNormalCase($sheet, $row, $columnMap, $title){
//print_r("title = $title in handleNormalCase\n");
		$case = array('testcase_type'=>$this->testcase_type);
		foreach($columnMap['case'] as $field=>$col){
			$case[$field] = $this->getCell($sheet, $row, $col);
		}
		if (empty($this->parse_result[$title]))
			$this->parse_result[$title] = array('format'=>'normal');
		$this->parse_result[$title]['testcase'][$row] = $case;
	}

	protected function handleNormalTestResult($sheet, $row, $columnMap, $title){
		$result = trim(strtolower($this->getCell($sheet, $row, $columnMap['test_result']['result'])));
		if (empty($result))
			return;
		$comment = $this->getCell($sheet, $row, $columnMap['test_result']['comment']);
		$this->parse_result[$title]['test_result'][$row][$columnMap['test_result']['result']] = array('result'=>$result, 'comment'=>$comment);
	}
};
?>