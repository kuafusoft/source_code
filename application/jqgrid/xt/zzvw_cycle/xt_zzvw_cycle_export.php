<?php
require_once('base_report.php');

class cycle_export extends base_report{
	private $cycleInfo = array();
	private $testResult = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
		
		$ntStyle = $this->styles['normal'];
		$ntStyle['font']['color'] = array('argb' => 'FFAAAAAA');
		$passStyle = $this->styles['normal'];
		$passStyle['font']['color'] = array('argb' => 'FF0000FF');
		$nsStyle = $this->styles['normal'];
		$failStyle = $this->styles['normal'];
		$failStyle['font']['color'] = array('argb' => 'FFFF0000');
		$failStyle['font']['bold'] = true;
		$indexStyle = array(
        	'font' => array(
        		'size'=>12,
        		'bold' => true,
			),
        	'alignment' => array(
        		'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
        	),
        	'borders' => array(
        		'allborders' => array(
        			'style' => PHPExcel_Style_Border::BORDER_THIN,
        		),
        	),
        	'fill' => array(
        		'type' => PHPExcel_Style_Fill::FILL_SOLID,
        		'color' => array(
        			'argb' => 'FFF7F7F7',
        		),
        	),
		);

        $this->styles['result_nt'] = $ntStyle;
        $this->styles['result_pass'] = $passStyle;
        $this->styles['result_fail'] = $failStyle;
        $this->styles['result_ns'] = $nsStyle;
		$this->styles['index'] = $indexStyle;
	}
	
	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$header = array();
		switch($worksheetIndex){
			case 0:
				$header = array(
					array('label'=>'Cycle', 'width'=>150, 'index'=>'name'),
					array('label'=>'Feature', 'width'=>150, 'index'=>'cycle_type'),
					array('label'=>'Project Version', 'width'=>150, 'index'=>'prj'),
					array('label'=>'OS Version', 'width'=>150, 'index'=>'os'),
					array('label'=>'Compiler Version', 'width'=>150, 'index'=>'compiler'),
					array('label'=>'Board Version', 'width'=>150, 'index'=>'board_type'),
					array('label'=>'Release Version', 'width'=>150, 'index'=>'rel'),
					array('label'=>'Tester Name', 'width'=>150, 'index'=>'testers'),
					array('label'=>'Pass Case', 'width'=>150, 'index'=>'pass_cases'),
					array('label'=>'Fail Case', 'width'=>150, 'index'=>'fail_cases'),
					array('label'=>'Total Case', 'width'=>150, 'index'=>'total_cases'),
					array('label'=>'Pass Rate', 'width'=>150, 'index'=>'pass_rate'),
					array('label'=>'Test Time', 'width'=>150, 'index'=>'test_time')
				);
				break;
				
			case 1:
				$header = array(
					array('label'=>'Module', 'width'=>150, 'index'=>'testcase_module'),
					array('label'=>'TestPoint', 'width'=>150, 'index'=>'testcase_testpoint'),
					//array('label'=>'Testcase', 'width'=>150, 'index'=>'testcase'),
					array('label'=>'Testcase', 'width'=>150, 'index'=>'code'),
					array('label'=>'Case Version', 'width'=>150, 'index'=>'ver'),
					array('label'=>'Result', 'width'=>150, 'index'=>'result_type'),
					array('label'=>'StartTime', 'width'=>150, 'index'=>'start_time'),
					array('label'=>'Duration', 'width'=>150, 'index'=>'duration_seconds'),
					array('label'=>'Deadline', 'width'=>150, 'index'=>'deadline'),
					array('label'=>'Priority', 'width'=>150, 'index'=>'testcase_priority'),
					//array('label'=>'Category', 'width'=>150, 'index'=>'testcase_category'),
					array('label'=>'Test ENV', 'width'=>150, 'index'=>'test_env'),
					array('label'=>'Auto Level', 'width'=>150, 'index'=>'auto_level'),
					array('label'=>'Defects', 'width'=>150, 'index'=>'defect_ids'),
					//array('label'=>'Tester', 'width'=>150, 'index'=>'tester_id'),
					array('label'=>'Comment', 'width'=>150, 'index'=>'comment'),
					//array('label'=>'Objective', 'width'=>150, 'index'=>'objective'),
					//array('label'=>'Precondition', 'width'=>150, 'index'=>'precondition'),
					//array('label'=>'Expected_result', 'width'=>150, 'index'=>'expected_result')
				);
				break;
		}
		parent::setColumnHeader(array('rows'=>array($header)), $worksheetIndex);
		parent::_writeHeader($worksheetIndex);
	}
	
	protected function getData($sheetIndex){
		$data = array();
		$element = implode(",", json_decode($this->params['element']));
		switch($sheetIndex){
			case 0:
				$sql =  "SELECT cycle.*, cycle_type.name as cycle_type, prj.name as prj, os.name as os, rel.name as rel, compiler.name as compiler, board_type.name as board_type ".
				" FROM zzvw_cycle cycle left join cycle_type on cycle.cycle_type_id=cycle_type.id".
				" LEFT JOIN prj on cycle.prj_id=prj.id ".
				" LEFT JOIN os on prj.os_id=os.id ".
				" LEFT JOIN rel on cycle.rel_id=rel.id".
				" LEFT JOIN compiler on cycle.compiler_id=compiler.id".
				" LEFT JOIN board_type on cycle.board_type_id=board_type.id".
				" WHERE cycle.id in (".$element.")";
				$res = $this->params['db']->query($sql);
				$this->cycleInfo = $res->fetch();
				if(!isset($this->cycleInfo['testers']))
					$this->cycleInfo['testers'] = '';
				$userAdmin = new Application_Model_Useradmin($this);
				$users = $userAdmin->getUsers($this->cycleInfo['tester_ids']);
				if (!empty($users)){
					$tester = array();
					foreach($users as $user){
						$tester[] = $user['nickname'];
					}
					$this->cycleInfo['testers'] = implode(',', $tester);
				}
				if(!isset($this->cycleInfo['test_time']))
					$this->cycleInfo['test_time'] = $this->cycleInfo['start_date'].'~'.$this->cycleInfo['end_date'];
				$data[] = $this->cycleInfo;
				break;
			case 1:
				$sql =  "SELECT detail.cycle_id as cycle_id, testcase_module.name as testcase_module, detail.testcase_module_id as testcase_module_id".
				" FROM zzvw_cycle_detail detail LEFT JOIN testcase_module on detail.testcase_module_id=testcase_module.id".
				" WHERE detail.cycle_id in (".$element.")";
				$res = $this->params['db']->query($sql);
				while($row = $res->fetch()){
					$this->testResult[$row['testcase_module_id']] = $this->calcCases($row);
				}
				break;
			case 2:
				$sql =  "SELECT detail.*, testcase_module.name as testcase_module, testcase_testpoint.name as testcase_testpoint".
				", result_type.name as result_type, testcase_priority.name as testcase_priority, test_env.name as test_env, auto_level.name as auto_level".
				" FROM zzvw_cycle_detail detail left join testcase_module on detail.testcase_module_id=testcase_module.id".
				" LEFT JOIN testcase_testpoint on detail.testcase_testpoint_id=testcase_testpoint.id".
				" LEFT JOIN result_type on detail.result_type_id=result_type.id".
				" LEFT JOIN testcase_priority on detail.testcase_priority_id=testcase_priority.id".
				" LEFT JOIN test_env on detail.test_env_id=test_env.id".
				" LEFT JOIN auto_level on detail.auto_level_id=auto_level.id".
				" WHERE detail.cycle_id in (".$element.")";
				$res = $this->params['db']->query($sql);
				while($row = $res->fetch()){
					$data[] = $row;//array();
				}
			break;
//print_r($data);
		}
//print_r($data);		
		return $data;
	}
	
	protected function _report($res, $sheetIndex){
		switch($sheetIndex){
			case 0:
				$this->writeRow($res,$sheetIndex);
				break;
			default:
				parent::_report($res, $sheetIndex);
		}
	}
	
	protected function writeRow($content, $sheetIndex = 0, $defaultStyle = array(), $contentKey = null){
		switch($sheetIndex){
			case 0:
				$this->writeCover($content);
				break;
			default:
				parent::writeRow($content, $sheetIndex);
		}
	}
	private function writeCover($res){
		$row = 4;
		$col = 1;
		$this->objExcel->setActiveSheetIndex(0);
		$sheet = $this->objExcel->getActiveSheet();
		
		//parent::writeRow($content, $sheetIndex);
		
		$this->writeCell(0, $row, $col, 'Test environment configuration', 'title');
		$this->writeCell(0, $row ++, $col + 1, '', 'title');
		$sheet->mergeCells('B4:C4');
		foreach($res as $cells){
			$this->writeCell(0, $row, $col++, $cells['name'], 'index');
			//$this->writeCell(0, $row++, $col++, $cells['v']);
			$col = 1;
		}
		
		$row += 4;
		$this->writeCell(0, $row, $col, 'Test Result', 'title');
		$this->writeCell(0, $row, $col + 1, '', 'title');
		$this->writeCell(0, $row, $col + 2, '', 'title');
		$this->writeCell(0, $row, $col + 3, '', 'title');
		$this->writeCell(0, $row, $col + 4, '', 'title');
		$this->writeCell(0, $row, $col + 5, '', 'title');
		$this->mergeCells(0, $row, $col, $row, $col + 5);
		
		$row++;
		$this->writeCell(0, $row, $col, 'Module', 'index');
		$this->writeCell(0, $row, $col + 1, 'Total Cases', 'index');
		$this->writeCell(0, $row, $col + 2, 'Pass Cases', 'index');
		$this->writeCell(0, $row, $col + 3, 'Pass_rate', 'index');
		$this->writeCell(0, $row, $col + 4, 'Fail Cases', 'index');
		$this->writeCell(0, $row, $col + 5, 'NT Cases', 'index');
		$sheet->getRowDimension($row)->setRowHeight(-1);
		
		$col = 1;
		$row++;
		$startRow = $row;
		foreach($this->testResult as $k=>$result){
			foreach($result as $r=>$v){
				$this->writeCell(0, $row, $col++, $v);
			}
			$col = 1;
			$row ++;
		}
		$this->writeCell(0, $row, 1, "Total", 'index');
		$this->writeCell(0, $row, 2, "=sum(C".$startRow.":C".($row - 1).")", '');
		$this->writeCell(0, $row, 3, "=sum(D".$startRow.":D".($row - 1).")", '');
		$this->writeCell(0, $row, 4, '');
		$this->writeCell(0, $row, 5, "=sum(F".$startRow.":F".($row - 1).")", '');
		$this->writeCell(0, $row, 6, "=sum(G".$startRow.":G".($row - 1).")", '');
		
		$sheet->getColumnDimensionByColumn($col)->setWidth(30);
		$sheet->getColumnDimensionByColumn($col+1)->setWidth(30);
		$sheet->getColumnDimensionByColumn($col+2)->setWidth(30);
		$sheet->getColumnDimensionByColumn($col+3)->setWidth(30);
		$sheet->getColumnDimensionByColumn($col+4)->setWidth(30);
		$sheet->getColumnDimensionByColumn($col+5)->setWidth(30);
	}
	
	public function calcCases($row){
		$result = array();
		$result['testcase_module'] = $row['testcase_module'];
		$res = $this->params['db']->query("SELECT COUNT(*) as total FROM zzvw_cycle_detail WHERE cycle_id={$row['cycle_id']} and testcase_module_id={$row['testcase_module_id']}");
		$info = $res->fetch();
		$result['total']= $info['total'];
		$res = $this->params['db']->query("SELECT COUNT(*) as pass FROM zzvw_cycle_detail WHERE cycle_id={$row['cycle_id']} and testcase_module_id={$row['testcase_module_id']} and result_type_id=".RESULT_TYPE_PASS);
		$info = $res->fetch();
		$result['pass']= $info['pass'];
		
		if($result['pass']>0 && $result['total']>0)
			$result['passrate'] = ($result['pass']/$result['total'])*100;
		else
			$result['passrate'] = 0;
		
		$res = $this->params['db']->query("SELECT COUNT(*) as fail FROM zzvw_cycle_detail WHERE cycle_id={$row['cycle_id']} and testcase_module_id={$row['testcase_module_id']} and result_type_id=".RESULT_TYPE_FAIL);
		$info = $res->fetch();
		$result['fail'] = $info['fail'];
		
		$result['NT'] = $result['total'] - $result['pass'] - $result['fail'];
		return $result;
	}
}
