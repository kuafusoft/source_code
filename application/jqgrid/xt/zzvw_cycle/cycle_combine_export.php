<?php
require_once(str_replace("\\", "/", dirname(__FILE__)).'/cycle_contrast_export.php');
//for same prj combine export

class cycle_combine_export extends cycle_contrast_export{
	private $data = array();
	private $statisticsHeader = array();
	private $cycleInfo = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
	}

	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$title1 = array(
			array('label'=>'Codec Stream', 'width'=>200, 'index'=>'codec_stream'),
			array('label'=>'Test Case Title', 'width'=>400, 'index'=>'testcase_title'),
			array('label'=>'Module', 'width'=>200, 'index'=>'module'),
			array('label'=>'Test Env', 'width'=>200, 'index'=>'test_env')
		);

		$even = true;
		$str_cycleIds = implode(',', json_decode($this->params['element']));
		$sql = "SELECT cycle.*, prj.name as prj, chip.name as chip, board_type.name as board_type, os.name as os".
			" FROM cycle LEFT JOIN prj ON cycle.prj_id=prj.id".
			" LEFT JOIN chip ON prj.chip_id=chip.id".
			" LEFT JOIN board_type ON prj.board_type_id=board_type.id".
			" LEFT JOIN os ON prj.os_id=os.id".
			" WHERE cycle.id IN ($str_cycleIds) ORDER BY cycle.prj_id ASC, cycle.end_date ASC";
		$res = $this->params['db']->query($sql);
		while($row = $res->fetch()){
			$even_style = 'odd';
			if ($even == true){
				$even_style = 'even';
			}
			$even = !$even;
			$this->cycleInfo[$row['id']] = $row;//存下来干什么
			$userAdmin = new Application_Model_Useradmin($this);
			$tester_ids = explode(",", $row['tester_ids']);
			foreach($tester_ids as $k=>$val){
				if(empty($val)){
					unset($tester_ids[$k]);
				}
			}
			$row['tester_ids'] = implode(",", $tester_ids);
			$resultTester = null;
			if(!empty($row['tester_ids'])){
				$sqlTester = "SELECT GROUP_CONCAT(DISTINCT nickname) as tester FROM users WHERE id IN (".$row['tester_ids'].")";
				$resTester = $userAdmin->db->query($sqlTester);
				$resultTester = $resTester->fetch();
			}
			
			$sqlCount = "SELECT count(*) as cc FROM cycle_detail WHERE cycle_id=".$row['id'];
			$resCount = $this->params['db']->query($sqlCount);
			$resultCount = $resCount->fetch();
			
			$sqlFinish = "SELECT count(*) as cc FROM cycle_detail WHERE cycle_id=".$row['id'].' AND result_type_id != 0';
			$resFinish = $this->params['db']->query($sqlFinish);
			$resultFinish = $resFinish->fetch();
		
			$sqlPass = "SELECT count(*) as cc FROM cycle_detail WHERE cycle_id=".$row['id'].' AND result_type_id=1';
			$resPass = $this->params['db']->query($sqlPass);
			$resultPass = $resPass->fetch();

			if ($resultFinish['cc'] < $resultCount['cc']){ // not finished
				$row['name'] .= '(*)';
			}
			
			$finishRate = $passRate = 0.00;
			if($resultCount['cc']){
				$finishRate = $resultFinish['cc'] / $resultCount['cc'];
				$passRate = $resultPass['cc'] / $resultCount['cc'];
			}

			$this->data[0]['cycle'][$row['prj']][$row['os']][$row['id']] = array(
				'Cycle'=>$row['name'],
				'Start Date'=>$row['start_date'],
				'End Date'=>$row['end_date'],
				'Finish Rate'=>$finishRate,
				'Pass Rate'=>$passRate,
//				'Open Bugs'=>$openBugs,
				'Tester'=>$resultTester['tester']
			);
		}
		$title1[] = array('label'=>'Last Result', 'width'=>500, 'index'=>'last_result');
		$title1[] = array('label'=>'Comment', 'width'=>500, 'index'=>'comment');
		$title1[] = array('label'=>'CRID', 'width'=>500, 'index'=>'defect_ids');

		if ($worksheetIndex == 1){
			$this->setColumnHeader(array('rows'=>array($title1)), 1, 1, 0);
			base_report::_writeHeader(1);
		}
		else if ($worksheetIndex == 0){
		}
/*
		else if ($worksheetIndex == 2){
			$this->setColumnHeader($title2, 2, 1, 0);
			parent::_writeHeader(2);
		}
*/		
	}

	protected function postProcess($worksheetIndex = 0){
		if ($worksheetIndex == 0){
			if(isset($this->data[$worksheetIndex]['module'])){
				// write the CR table
				// $this->writeCR($worksheetIndex);
				
				// write the cycle information
				$this->writeCycle($worksheetIndex);
			}
		}
	}
	
	private function writeCycle($worksheetIndex = 0){
    	$this->nextRow[0] = 2;
		$column = 1;
		$this->nextRow[0]++;
		$this->writeCell($worksheetIndex, $this->nextRow[0], $column, "Prj:", 'summary');
		$column_start = $column;
		$flag = 1;
		foreach($this->data[0]['cycle'] as $prj=>$prj_v){
			$this->writeCell($worksheetIndex, $this->nextRow[0], $column + 1, $prj, 'summary');
			foreach($prj_v as $os=>$os_v){
				foreach($os_v as $cycleInfo){
					$row = $this->nextRow[0] + 1;
					foreach($cycleInfo as $key=>$item){
						$style = 'normal';
						if($key == 'Finish Rate' || $key == 'Pass Rate') {
							$style = 'percent';
						}
						if($flag == 1)
							$keys[] = $key;
						//$this->writeCell($worksheetIndex, $row, $column, $key, 'right');
						$this->writeCell($worksheetIndex, $row, $column + 1 , $item, $style);
						$this->writeCell($worksheetIndex, $row, $column + 2, '');
						$this->writeCell($worksheetIndex, $row, $column + 3, '');
						$this->mergeCells($worksheetIndex, $row, $column + 1 , $row, $column + 3);
						$row++;
					}
					$flag = 2;
					$column += 3;
				}
			}
		}
		$row = $this->nextRow[0] + 1;
		foreach($keys as $key){
			$this->writeCell($worksheetIndex, $row, $column_start, $key, 'right');
			$row++;
		}
		for($i=$column_start+2; $i<=$column; $i++)
			$this->writeCell($worksheetIndex, $this->nextRow[0], $i, '', 'summary');
		$this->mergeCells($worksheetIndex, $this->nextRow[0], $column_start+1, $this->nextRow[0], $column);
		for($i=$column_start+2; $i<=$column; $i++)
			$this->writeCell($worksheetIndex, $this->nextRow[0], $i, '', 'total');
	}
	protected function getData($worksheetIndex){//worksheetindex=1时没什么问题
		$cr = array();
		$strCycleIds = implode(',', json_decode($this->params['element']));

		if ($worksheetIndex == 1){
			$sql = "SELECT detail.id, detail.finish_time, testcase_module.id as module_id, testcase_module.name as module, detail.testcase_id as tc_id, testcase.id as testcase_id, testcase.code as testcase_title,".
				" result_type.name as result_type, detail.result_type_id as result_type_id, detail.comment as comment, detail.defect_ids as defect_ids, cycle.id as cycle_id, cycle.name as cycle,".
				" detail.test_env_id as test_env_id, test_env.name as test_env, detail.codec_stream_id as codec_stream_id, codec_stream.name as codec_stream".
				" FROM cycle_detail detail LEFT JOIN testcase ON detail.testcase_id=testcase.id".
				" LEFT JOIN testcase_module ON testcase_module.id=testcase.testcase_module_id".
				" LEFT JOIN test_env ON test_env.id=detail.test_env_id".
				" LEFT JOIN codec_stream ON codec_stream.id=detail.codec_stream_id".
				" LEFT JOIN result_type ON result_type.id=detail.result_type_id".
				" LEFT JOIN cycle ON cycle.id=detail.cycle_id".
				" WHERE detail.cycle_id IN ($strCycleIds)".
				" ORDER BY detail.codec_stream_id ASC, testcase.testcase_module_id ASC, detail.testcase_id ASC";
			$res = $this->params['db']->query($sql);

			while($row = $res->fetch()){
				if(!empty($row['test_env'])){
					$row['tc_id'] .= '_'.$row['test_env'];
				}
				if(!empty($row['codec_stream'])){
					$row['tc_id'] .= '_'.$row['codec_stream'];
				}
				if(!empty($row['test_env']) && !empty($row['codec_stream'])){
					$row['tc_id'] .= '_'.$row['test_env'].'_'.$row['codec_stream'];
				}
				$this->data[1][$row['tc_id']]['module'] = $this->data[0]['module'][$row['module_id']]['module'] = $row['module'];
				$this->data[1][$row['tc_id']]['test_env'] = $row['test_env'];//$this->data[0]['module'][$row['test_env_id']]['test_env'] = $row['test_env'];
				$this->data[1][$row['tc_id']]['codec_stream'] = $row['codec_stream'];//$this->data[0]['module'][$row['codec_stream_id']]['codec_stream'] = $row['codec_stream'];
				$this->data[1][$row['tc_id']]['testcase_id'] = $row['testcase_id'];
				$this->data[1][$row['tc_id']]['testcase_title'] = $row['testcase_title'];
				
				if(isset($this->data[1][$row['tc_id']]['last_result'])){
					if($this->data[1][$row['tc_id']]['finish_time'] < $row['finish_time']){
						$this->data[1][$row['tc_id']]['last_result'] = $row['result_type'];
						if($row['result_type_id'] == 0)
							$this->data[1][$row['tc_id']]['last_result'] = 'blank';
						$this->data[1][$row['tc_id']]['finish_time'] = $row['finish_time'];
					}
				}else{
					$this->data[1][$row['tc_id']]['last_result'] = $row['result_type'];
					if($row['result_type_id'] == 0)
						$this->data[1][$row['tc_id']]['last_result'] = 'blank';
					$this->data[1][$row['tc_id']]['finish_time'] = $row['finish_time'];
				}
				
				if (!empty($row['defect_ids'])){
					if (empty($this->data[1][$row['tc_id']]['defect_ids']))
						$this->data[1][$row['tc_id']]['defect_ids'] = "";
					else
						$this->data[1][$row['tc_id']]['defect_ids'] .= "\n";
					$this->data[1][$row['tc_id']]['defect_ids'] .= $row['cycle'].":".$row['defect_ids'];
					
					if (empty($this->data[0]['defect_ids'][$row['defect_ids']]))
						$this->data[0]['defect_ids'][$row['defect_ids']] = '';
					else
						$this->data[0]['defect_ids'][$row['defect_ids']] .= ',';
					$this->data[0]['defect_ids'][$row['defect_ids']] .= $row['module'].":".$row['defect_ids'];
					$this->data[0]['cycle_cr'][$row['cycle_id']][] = $row['defect_ids'];
					$cr[$row['module_id']][$row['cycle_id'].'_crid'][] = $row['defect_ids'];
				}
				if (!empty($row['comment'])){
					if (empty($this->data[1][$row['tc_id']]['comment']))
						$this->data[1][$row['tc_id']]['comment'] = "";
					$this->data[1][$row['tc_id']]['comment'] .= $row['cycle'].":".$row['comment']."\n";
				}
				
				if (empty($this->data[0]['module'][$row['module_id']][$row['cycle_id'].'_'.$row['result_type']]))
					$this->data[0]['module'][$row['module_id']][$row['cycle_id'].'_'.$row['result_type']] = 0;
				$this->data[0]['module'][$row['module_id']][$row['cycle_id'].'_'.$row['result_type']] ++;
				if (empty($this->data[0]['module'][$row['module_id']][$row['cycle_id']]))
					$this->data[0]['module'][$row['module_id']][$row['cycle_id']] = 0;
				$this->data[0]['module'][$row['module_id']][$row['cycle_id']] ++;
			}
		}
		if(isset($this->data[0]['module'])){
			foreach($cr as $module_id=>$v){
				foreach($v as $cycle_id=>$c){
					$c = array_unique($c);
					$this->data[0]['module'][$module_id][$cycle_id] = implode(',', $c);
				}
			}
		}	
		if(isset($this->data[$worksheetIndex]))
			return $this->data[$worksheetIndex];
	}
}
?>
