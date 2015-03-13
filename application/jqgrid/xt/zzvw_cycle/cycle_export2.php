<?php
require_once('base_report.php');

class cycle_export2 extends base_report{
	private $data = array();
	private $statisticsHeader = array();
	private $cycleInfo = array();
	public function __construct($sheetTitles, $params = array()){
		parent::__construct($sheetTitles, $params);
		$this->styles['percent'] = $this->styles['total'];
		$this->styles['percent']['numberformat'] = array('code'=>PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
		$this->styles['module'] = $this->styles['normal'];
		$this->styles['module']['font']['bold'] = true;
		$this->styles['module']['font']['size'] ++;
		
		$this->styles['even'] = $this->styles['normal'];
		$this->styles['even']['fill']['color'] = array('argb' => 'FFB6DDE8');
		
		$this->styles['odd'] = $this->styles['normal'];
		$this->styles['odd']['fill']['color'] = array('argb' => 'FFDBEEFA');
		
		$this->styles['nt']= $this->styles['normal'];
		$this->styles['nt']['fill']['color'] = array('argb' => 'FFAAAAAA');
	}

	protected function _writeHeader($worksheetIndex, $setWidth = true, $mergeColumns = array()){
		$title1 = array(
			array('label'=>'Test Case ID', 'width'=>300, 'index'=>'testcase_id'),
			array('label'=>'Module', 'width'=>200, 'index'=>'module'),
			array('label'=>'Test Env', 'width'=>200, 'index'=>'test_env'),
			array('label'=>'Codec Stream', 'width'=>200, 'index'=>'codec_stream'),
			// array('label'=>'Test Case ID', 'width'=>300, 'index'=>'testcase_id'),
			array('label'=>'Test Case Title', 'width'=>400, 'index'=>'testcase_title'),
		);

		$even = true;
		$str_cycleIds = implode(',', json_decode($this->params['element']));
		$sql = "SELECT cycle.*, prj.name as prj, chip.name as chip, board_type.name as board_type, os.name as os".
			" FROM cycle LEFT JOIN prj ON cycle.prj_id=prj.id".
			" LEFT JOIN chip ON prj.chip_id=chip.id".
			" LEFT JOIN board_type ON prj.board_type_id=board_type.id".
			" LEFT JOIN os ON prj.os_id=os.id".
			" WHERE cycle.id in ($str_cycleIds) ORDER BY prj ASC, cycle.end_date ASC";
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
			$sqlTester = "SELECT GROUP_CONCAT(DISTINCT nickname) as tester FROM users WHERE id IN (".$row['tester_ids'].")";
			$resTester = $userAdmin->db->query($sqlTester);
			$resultTester = $resTester->fetch();
			
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
		$title1[] = array('label'=>'CRID', 'width'=>500, 'index'=>'crid');

		if ($worksheetIndex == 1){
			$this->setColumnHeader(array('rows'=>array($title1)), 1, 1, 0);
			parent::_writeHeader(1);
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

	protected function calcStyle(&$content, $sheetIndex, $defaultStyle = array()){
		$style = parent::calcStyle($content, $sheetIndex, $defaultStyle);
		if ($sheetIndex == 1){
			foreach($this->columnHeaders[1]['rows'][0] as $header){
				$key = $header['index'];
				if (strpos($key, '_result') !== false && isset($content[$key]) && $content[$key] != 'Pass'){
					$style[$key] = 'warning';
					if($content[$key] == 'N/T')	
						$style[$key] = 'nt';
					// $this->checkStyle($content[$key], '', $style[$key]);
				}
			}
		}
		return $style;
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
	
	
	// private function writeCR($worksheetIndex = 0){
		// $cr_module = array();
		// $cr_cycle = array();
		// if (!empty($this->data[0]['crid'])){
			// foreach($this->data[0]['crid'] as $crid=>$module){
				// $crids = explode(',', $crid);
				// $modules = explode(',', $module);
				// foreach($crids as $c){
					// $len = strlen($c);
					// if ($len < 12){
						// if (preg_match('/^ENGR(.*)$/', $c, $matches)){
							// $c = 'ENGR'.str_pad($matches[1], 8, "0", STR_PAD_LEFT);
						// }
					// }
					// $c = "'".$c."'";
					// foreach($modules as $m){
						// $cr_module[$c][] = $m;
					// }
				// }
			// }
			// $cm = array();
			// foreach($cr_module as $c=>$m){
				// $um = array_unique($m);
				// $cm[$c] = implode(',', $um);
			// }
			// //$this->data[0]['module'][$row['moduleid']][$row['cycleid'].'_crid']	
			// $crIds = array_keys($cm);		
	// //print_r($crIds);
			// $strCRs = implode(',', $crIds);
	// //print_r($strCRs);					
			// $column = $this->columnHeaderStartColumn[$worksheetIndex];
			// $this->nextRow[0] += 3;
			// $sevirityBugs = 0;
			// $sqlCR = "SELECT DISTINCT CR.id, CR.headline, CR.status, CR.originteam, CR.severity, CR.origsubmitdate, USERS.fullname, BLN.name AS baseline".
				// "  FROM cqfsl_engr.cr CR LEFT JOIN cqfsl_engr.baseline BLN ON CR.originbaseline=BLN.dbid ".
				// "  LEFT JOIN cqfsl_engr.USERS USERS ON CR.origsubmitter=USERS.dbid ".
				// " WHERE CR.subtype='Defect' AND CR.id IN ($strCRs)";
			// $resCR = $this->params['cq_dbh']->query($sqlCR, $totalRecords);
			// $cr = array();
			// $i = 0;
			// while($row = $resCR->fetch()){
				// $row['module'] = $cm["'".$row['id']."'"];
				// $cr[$cm["'".$row['id']."'"].'_'.$i ++] = $row;
				// if ($row['severity'] < 3)
					// $sevirityBugs ++;
			// }
	// //print_r($cr);
			// ksort($cr);
	// //print_r($cr);				
			// if ($totalRecords > 0){
				// $this->writeCell($worksheetIndex, $this->nextRow[0], $column, 'Issues: total '.$totalRecords.' bug(s) ('.$sevirityBugs.' S1/S2)', 'summary');
				// $this->mergeCells($worksheetIndex, $this->nextRow[0], $column, $this->nextRow[0], $column + 16);
				// $this->objExcel->getActiveSheet()->getRowDimension($this->nextRow[0])->setRowHeight(30);
				// $this->nextRow[0] ++;
				// $header = array(
					// array('label'=>'ID', 'width'=>250, 'index'=>'id'),
					// array('label'=>'Module', 'width'=>200, 'index'=>'module', 'columns'=>2),
	// //						array('label'=>'Affected Configs', 'width'=>250, 'index'=>'configsaffected'),
					// array('label'=>'Headline', 'width'=>500, 'index'=>'headline', 'columns'=>4),
					// array('label'=>'Severity', 'width'=>100, 'index'=>'severity', 'columns'=>2),
					// array('label'=>'Current Status', 'width'=>150, 'index'=>'status'),
					// array('label'=>'Origin Submit Date', 'width'=>200, 'index'=>'origsubmitdate'),
					// array('label'=>'Baseline', 'width'=>200, 'index'=>'baseline', 'columns'=>4),
					// array('label'=>'Origin Submitter', 'width'=>250, 'index'=>'fullname'),
					// array('label'=>'Origin Team', 'width'=>250, 'index'=>'originteam'),
				// );
				// $this->setColumnHeader($header, 0, $this->nextRow[0], $column);
				// parent::_writeHeader(0, false);
				// parent::_report($cr, 0);
			// }
		// }
	// }
	
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
		// write the statistics data		
		$this->setColumnHeader(array('rows'=>array($this->statisticsHeader)), 0, 1, 1);
		$this->data[2][0]['class'] = 'New CR Number';		
		$this->data[2][1]['class'] = 'CR Number';
		$this->data[2][2]['class'] = 'Pass Rate';
		// foreach($this->columnHeaders[0] as $ch){
			// if ($ch['index'] == 'class'){
				// $column ++;
				// continue;
			// }
			// $cycle_Id = $ch['index'];
			// $this->data[2][0][$cycle_Id] = 0;
			// if (!empty($this->data[0]['cycle_cr'][$cycle_Id])){			
				// $cycle_cr = array_unique(explode(',', implode(',', $this->data[0]['cycle_cr'][$cycle_Id])));
				// $strCRs = "'".implode("','", $cycle_cr)."'";
				// $sqlCR = "SELECT count(*) as cc".
					// " FROM cqfsl_engr.cr".
					// " WHERE subtype='Defect' AND id IN ($strCRs) AND origsubmitdate>='".$this->cycleInfo[$cycle_Id]['starttime']."'";
				// $resCR = $this->params['cq_dbh']->query($sqlCR, $totalRecords);
				// $this->data[2][0][$cycle_Id] = $totalRecords;
			// }
		// }				
//print_r($this->data[2]);				
		parent::_report($this->data[2], 0);
		$column = $this->columnHeaderStartColumn[0];
		foreach($this->columnHeaders[0] as $ch){
			$LN = $this->RC2LN(4, $column ++);
			$this->objExcel->getActiveSheet()->getStyle($LN)
				->getNumberFormat()
				->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_PERCENTAGE);
		}
	}
	
    protected function _report($res, $worksheetIndex){
    	switch($worksheetIndex){
			case 1:
				return parent::_report($res, $worksheetIndex);
			case 0:
			if(isset($res['module']))
				return parent::_report($res['module'], $worksheetIndex);
		}
	}

	protected function getData($worksheetIndex){//worksheetindex=1时没什么问题
		$cr = array();
		$strCycleIds = implode(',', json_decode($this->params['element']));

		if ($worksheetIndex == 1){
			$sql = "SELECT detail.id, detail.finish_time, testcase_module.id as module_id, testcase_module.name as module, detail.testcase_id as dtc_id, testcase.id as testcase_id, testcase.code as testcase_title,".
				" result_type.name as result_type, detail.result_type_id as result_type_id, detail.comment as comment, detail.defect_ids as defect_ids, cycle.id as cycle_id, cycle.name as cycle,".
				" detail.test_env_id as test_env_id, test_env.name as test_env, detail.codec_stream_id as codec_stream_id, codec_stream.name as codec_stream".
				" FROM cycle_detail detail LEFT JOIN testcase ON detail.testcase_id=testcase.id".
				" LEFT JOIN testcase_module ON testcase_module.id=testcase.testcase_module_id".
				" LEFT JOIN test_env ON test_env.id=detail.test_env_id".
				" LEFT JOIN codec_stream ON codec_stream.id=detail.codec_stream_id".
				" LEFT JOIN result_type ON result_type.id=detail.result_type_id".
				" LEFT JOIN cycle ON cycle.id=detail.cycle_id".
				" WHERE detail.cycle_id IN ($strCycleIds)".
				" ORDER BY module ASC, testcase_id ASC";
			$res = $this->params['db']->query($sql);

			while($row = $res->fetch()){
				if(!empty($row['test_env'])){
					$row['dtc_id'] .= '_'.$row['test_env'];
				}
				if(!empty($row['codec_stream'])){
					$row['dtc_id'] .= '_'.$row['codec_stream'];
				}
				if(!empty($row['test_env']) && !empty($row['codec_stream'])){
					$row['dtc_id'] .= '_'.$row['test_env'].'_'.$row['codec_stream'];
				}
				$this->data[1][$row['dtc_id']]['module'] = $this->data[0]['module'][$row['module_id']]['module'] = $row['module'];
				$this->data[1][$row['dtc_id']]['test_env'] = $row['test_env'];//$this->data[0]['module'][$row['test_env_id']]['test_env'] = $row['test_env'];
				$this->data[1][$row['dtc_id']]['codec_stream'] = $row['codec_stream'];//$this->data[0]['module'][$row['codec_stream_id']]['codec_stream'] = $row['codec_stream'];
				$this->data[1][$row['dtc_id']]['testcase_id'] = $row['testcase_id'];
				$this->data[1][$row['dtc_id']]['testcase_title'] = $row['testcase_title'];
				$this->data[1][$row['dtc_id']][$row['cycle_id'].'_result'] = $row['result_type'];
				if($row['result_type_id'] == 0)
					$this->data[1][$row['dtc_id']][$row['cycle_id'].'_result'] = 'N/T';
				
				if(isset($this->data[1][$row['dtc_id']]['last_result'])){
					if($this->data[1][$row['dtc_id']]['finish_time'] < $row['finish_time']){
						$this->data[1][$row['dtc_id']]['last_result'] = $row['result_type'];
						$this->data[1][$row['dtc_id']]['finish_time'] = $row['finish_time'];
					}
				}else{
					$this->data[1][$row['dtc_id']]['last_result'] = $row['result_type'];
					$this->data[1][$row['dtc_id']]['finish_time'] = $row['finish_time'];
				}
				
				if (!empty($row['defect_ids'])){
					if (empty($this->data[1][$row['dtc_id']]['defect_ids']))
						$this->data[1][$row['dtc_id']]['defect_ids'] = "";
					else
						$this->data[1][$row['dtc_id']]['defect_ids'] .= "\n";
					$this->data[1][$row['dtc_id']]['defect_ids'] .= $row['cycle'].":".$row['defect_ids'];
					
					if (empty($this->data[0]['defect_ids'][$row['defect_ids']]))
						$this->data[0]['defect_ids'][$row['defect_ids']] = '';
					else
						$this->data[0]['defect_ids'][$row['defect_ids']] .= ',';
					$this->data[0]['defect_ids'][$row['defect_ids']] .= $row['module'];
					$this->data[0]['cycle_cr'][$row['cycle_id']][] = $row['defect_ids'];
					$cr[$row['module_id']][$row['cycle_id'].'_crid'][] = $row['defect_ids'];
				}
				if (!empty($row['comment'])){
					if (empty($this->data[1][$row['dtc_id']]['comment']))
						$this->data[1][$row['dtc_id']]['comment'] = "";
					$this->data[1][$row['dtc_id']]['comment'] .= $row['cycle'].":".$row['comment']."\n";
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
	// private function checkStyle($name, $v, &$style){
		// switch($name){
			// case 'Pass':
				// $style = 'result_pass';
				// break;
			// case 'Fail':
				// $style = 'result_fail';
				// break;
			// case 'N/T':
				// $style = 'result_nt';
				// break;
			// case 'N/S':
				// $style = 'result_ns';
				// break;
			// case 'Pass_Rate':
				// $style = 'percent';
				// if(isset($v)){
					// if($v<=1 && $v>=0.8)
						// $style = 'high_percent';
					// else if($v<0.8 && $v>=0.6)
						// $style = 'middle_percent';
					// else if($v<0.6)
						// $style = 'low_percent';
				// }
				// break;
			// default:
				// break;
		// }
	// }
}
?>
