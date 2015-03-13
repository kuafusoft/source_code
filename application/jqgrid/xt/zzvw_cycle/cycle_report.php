<?php
require_once('base_report.php');

class cycle_report extends base_report{
	private $cycleInfo = array();
	private $testResult = array();
	private $allResult = array();
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

        $this->styles['result_nt'] = $ntStyle;
        $this->styles['result_pass'] = $passStyle;
        $this->styles['result_fail'] = $failStyle;
        $this->styles['result_ns'] = $nsStyle;
		
		$this->styles['percent'] = $this->styles['total'];
		$this->styles['percent']['numberformat'] = array('code'=>PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
		
		$sql =  "SELECT cycle.*, cycle_type.name as cycle_type, prj.name as prj, os.name as os, rel.name as rel, compiler.name as compiler, board_type.name as board_type ".
			" FROM zzvw_cycle cycle left join cycle_type on cycle.cycle_type_id=cycle_type.id".
			" LEFT JOIN prj on cycle.prj_id=prj.id ".
			" LEFT JOIN os on prj.os_id=os.id ".
			" LEFT JOIN rel on cycle.rel_id=rel.id".
			" LEFT JOIN compiler on cycle.compiler_id=compiler.id".
			" LEFT JOIN board_type on cycle.board_type_id=board_type.id".
			" WHERE cycle.id = {$this->params['element']}";
		$res = $this->params['db']->query($sql);
		$this->cycleInfo = $res->fetch();
		if(!isset($this->cycleInfo['testers']))
			$this->cycleInfo['testers'] = '';
		$userAdmin = new Application_Model_Useradmin($this);
		$tester_ids = explode(",", $this->cycleInfo['tester_ids']);
		foreach($tester_ids as $key=>$val){
			if(empty($val))
				unset($tester_ids[$key]);
		}
		$this->cycleInfo['tester_ids'] = implode(",", $tester_ids);
		$users = $userAdmin->getUsers($this->cycleInfo['tester_ids']);
		if (!empty($users)){
			$tester = array();
			foreach($users as $user){
				$tester[] = $user['nickname'];
			}
			$this->cycleInfo['testers'] = implode(',', $tester);
		}
	}
	
	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$header = array();
		switch($worksheetIndex){
			case 0:
				break;
				
			case 1:
				$header = array(
					array('label'=>'Module', 'width'=>150, 'index'=>'testcase_module'),
					array('label'=>'Test ENV', 'width'=>150, 'index'=>'test_env'),
					array('label'=>'Codec Stream', 'width'=>150, 'index'=>'codec_stream'),
					array('label'=>'TestPoint', 'width'=>150, 'index'=>'testcase_testpoint'),
					//array('label'=>'Testcase', 'width'=>150, 'index'=>'testcase'),
					array('label'=>'Testcase', 'width'=>150, 'index'=>'code'),
					array('label'=>'Case Version', 'width'=>150, 'index'=>'ver'),
					array('label'=>'Result', 'width'=>150, 'index'=>'result_type'),
					array('label'=>'Priority', 'width'=>150, 'index'=>'testcase_priority'),
					array('label'=>'Finish Time', 'width'=>150, 'index'=>'finish_time'),
					array('label'=>'Duration', 'width'=>150, 'index'=>'duration_seconds'),
					array('label'=>'Deadline', 'width'=>150, 'index'=>'deadline'),
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
		if($sheetIndex == 0){
			$data[] = array('name'=>'Cycle', 'v'=>$this->cycleInfo['name']);
			$data[] = array('name'=>'Feature', 'v'=>$this->cycleInfo['cycle_type']);
			$data[] = array('name'=>'Project Version', 'v'=>$this->cycleInfo['prj']);
			$data[] = array('name'=>'OS Version', 'v'=>$this->cycleInfo['os']);
			$data[] = array('name'=>'Compiler Version', 'v'=>$this->cycleInfo['compiler']);
			$data[] = array('name'=>'Board Version', 'v'=>$this->cycleInfo['board_type']);
			$data[] = array('name'=>'Release Version', 'v'=>$this->cycleInfo['rel']);
			$data[] = array('name'=>'Tester Name', 'v'=>$this->cycleInfo['testers']);
			
			$res = $this->params['db']->query("SELECT id, name FROM result_type WHERE name IS NOT NULL");
			$result = $res->fetchAll();
			$this->allresult = $this->calcCases('', $result);
			foreach($this->allresult as $key=>$val){
				$data[] = array('name'=>$key, 'v'=>$val);
			}
			$data[] = array('name'=>'Test Time', 'v'=>$this->cycleInfo['start_date'].'~'.$this->cycleInfo['end_date']);
			
			$sql =  "SELECT testcase_module.name as testcase_module, detail.testcase_module_id as testcase_module_id".
			" FROM zzvw_cycle_detail detail LEFT JOIN testcase_module on detail.testcase_module_id=testcase_module.id".
			" WHERE detail.cycle_id = {$this->params['element']}";
			$res = $this->params['db']->query($sql);
			while($row = $res->fetch()){
				$this->testResult[$row['testcase_module_id']] = $this->calcCases($row, $result);
			}
		}
		else if ($sheetIndex == 1){
			$sql =  "SELECT detail.*, testcase_module.name as testcase_module, testcase_testpoint.name as testcase_testpoint".
			", result_type.name as result_type, testcase_priority.name as testcase_priority, codec_stream.name as codec_stream, test_env.name as test_env, auto_level.name as auto_level".
			" FROM zzvw_cycle_detail detail left join testcase_module on detail.testcase_module_id=testcase_module.id".
			" LEFT JOIN testcase_testpoint on detail.testcase_testpoint_id=testcase_testpoint.id".
			" LEFT JOIN result_type on detail.result_type_id=result_type.id".
			" LEFT JOIN testcase_priority on detail.testcase_priority_id=testcase_priority.id".
			" LEFT JOIN test_env on detail.test_env_id=test_env.id".
			" LEFT JOIN codec_stream on detail.codec_stream_id=codec_stream.id".
			" LEFT JOIN auto_level on detail.auto_level_id=auto_level.id".
			" WHERE detail.cycle_id = {$this->params['element']}";
			$res = $this->params['db']->query($sql);
			while($row = $res->fetch()){
				$data[] = $row;
			}
		}		
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
		
		$this->writeCell(0, $row, $col, 'Test environment configuration', 'summary');
		$this->writeCell(0, $row ++, $col + 1, '', 'summary');
		$sheet->mergeCells('B4:C4');
		foreach($res as $cells){
			$style = '';
			$this->checkStyle($cells['name'], $cells['v'], $style);
			$this->writeCell(0, $row, $col++, $cells['name'], 'total');
			$this->writeCell(0, $row++, $col++, $cells['v'], $style);
			$col = 1;
		}
		
		$row += 4;
		$this->writeCell(0, $row, 1, 'Test Result', 'summary');
		$this->writeCell(0, $row+1, $col, 'Module', 'total');
		$col = 2;
		foreach($this->allresult as $key=>$val){
			$this->writeCell(0, $row, $col, '', 'summary');
			$this->writeCell(0, $row+1, $col++, $key, 'total');
		}
		$this->mergeCells(0, $row, 1, $row, $col-1);

		$row++;
		$sheet->getRowDimension($row)->setRowHeight(-1);
		
		$col = 1;
		$row++;
		$startRow = $row;
		foreach($this->testResult as $k=>$result){
			foreach($result as $r=>$v){
				$style = '';
				$this->checkStyle($r, $v, $style);
				$this->writeCell(0, $row, $col++, $v, $style);	
			}
			$col = 1;
			$row ++;
		}
		$this->writeCell(0, $row, 1, "Total", 'total');
		$sheet->getColumnDimensionByColumn(1)->setWidth(30);
		$col = 2;
		$asc = ord('C');
		foreach($this->allresult as $key=>$val){
			$char = chr($asc);
			$this->writeCell(0, $row, $col, "=sum(".$char.$startRow.":".$char.($row - 1).")", 'total');
			$sheet->getColumnDimensionByColumn($col++)->setWidth(30);
			$asc++;
		}
		$this->writeCell(0, $row, $col-1, "", 'total');
	}
	
	public function calcCases($module, $result_type){
		$result = array();
		//$result['testcase_module'] = $module['testcase_module'];
		$sql = "SELECT COUNT(*) as total FROM zzvw_cycle_detail WHERE cycle_id={$this->params['element']}";
		if(!empty($module)){
			$result['testcase_module'] = $module['testcase_module'];
			$sql .= " and testcase_module_id={$module['testcase_module_id']}";
		}
		$res = $this->params['db']->query($sql);
		$info = $res->fetch();
		$result['Total']= $info['total'];
		foreach($result_type as $cell){
			$sql = "SELECT COUNT(*) as count FROM zzvw_cycle_detail WHERE cycle_id={$this->params['element']}";
			if(!empty($module)){
				$sql .= " and testcase_module_id={$module['testcase_module_id']}";
			}
			$sql .= " and result_type_id=".$cell['id'];
			$res = $this->params['db']->query($sql);
			$info = $res->fetch();
			$result[$cell['name']]= $info['count'];
		}
		
		if($result['Pass']>0 && $result['Total']>0)
			$result['Pass_Rate'] = ($result['Pass']/$result['Total']);
		else
			$result['Pass_Rate'] = 0;
		return $result;
	}
	
	protected function calcStyle(&$content, $sheetIndex, $defaultStyle = array()){
		$style = parent::calcStyle($content, $sheetIndex, $defaultStyle);
		if ($sheetIndex == 1){
			foreach($this->columnHeaders[$sheetIndex]['rows'][0] as $header){
				$key = $header['index'];
				if (strpos($key, 'result_type') !== false && isset($content[$key]) && strtolower($content[$key]) != 'pass'){
					$style[$key] = 'warning';
					$this->checkStyle(strtolower($content[$key]), '', $style[$key]);
				}
			}
		}
		return $style;
	}
	
	private function checkStyle($name, $v, &$style){
		switch($name){
			case 'pass':
				$style = 'result_pass';
				break;
			case 'fail':
				$style = 'result_fail';
				break;
			case 'n/t':
				$style = 'result_nt';
				break;
			case 'n/s':
				$style = 'result_ns';
				break;
			case 'pass_rate':
				$style = 'percent';
				if(isset($v)){
					if($v<=1 && $v>=0.8)
						$style = 'high_percent';
					else if($v<0.8 && $v>=0.6)
						$style = 'middle_percent';
					else if($v<0.6)
						$style = 'low_percent';
				}
				break;
			default:
				break;
		}
	}
}
