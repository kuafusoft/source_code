<?php
require_once('base_report.php');
//for codec

class cycle_codec_export extends base_report{
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
		$this->statisticsHeader = array(
			array('label'=>'', 'width'=>300, 'index'=>'class', 'style'=>'module')
		);
		
		$title1 = array(
			array('label'=>'Module', 'width'=>200, 'index'=>'module'),
			array('label'=>'Codec Stream', 'width'=>200, 'index'=>'codec_stream'),
			array('label'=>'Test Env', 'width'=>200, 'index'=>'test_env'),
			array('label'=>'Test Case Title', 'width'=>400, 'index'=>'testcase_title'),//在加号的底下
			
		);
		$title0 = array(
			array('label'=>'Module', 'width'=>200, 'style'=>'module'),
		);
		$title0_1 = array(
			array('label'=>'', 'width'=>200, 'index'=>'module', 'style'=>'module'),
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
			
			$this->statisticsHeader[] = array('label'=>$row['name'], 'width'=>300, 'index'=>$row['id']);
			$info_res = $this->params['db']->query("SELECT id FROM cycle_detail WHERE cycle_id=".$row['id']);
			if($info = $info_res->fetch())
				$title1[] = array('label'=>$row['name'], 'width'=>300, 'index'=>$row['id'].'_result');//应该是一个总的result
			$title0[] = array('label'=>$row['name'], 'width'=>300, 'style'=>$even_style);
			foreach($this->params['results'] as $result){
				$title0[] = array('label'=>'', 'width'=>80, 'style'=>$even_style);
				$title0_1[] =  array('label'=>$result, 'width'=>80, 'index'=>$row['id'].'_'.strtolower($result), 'cases'=>$resultCount['cc'], 'style'=>$even_style);
			}
			$title0_1[] =  array('label'=>'CRID', 'width'=>100, 'index'=>$row['id'].'_crid', 'style'=>$even_style);

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
			parent::_writeHeader(1);
		}
		else if ($worksheetIndex == 0){
			$this->setColumnHeader(array('rows'=>array($this->statisticsHeader)), 0, 1, 1);
			parent::_writeHeader(0);

			$this->setColumnHeader(array('rows'=>array($title0)), 0, 8, 1);
			parent::_writeHeader(0);
			$this->setColumnHeader(array('rows'=>array($title0_1)), 0, 9, 1);
			parent::_writeHeader(0);
			// merge some cells
			$element = json_decode($this->params['element']);
			$lastColumn = count($element) * (count($this->params['results']) + 1) + 1;
			$this->writeCell($worksheetIndex, 7, 1, "General", 'summary');
			$this->objExcel->getActiveSheet()->getRowDimension(7)->setRowHeight(30);
			$this->mergeCells($worksheetIndex, 7, 1, 7, $lastColumn);
			
			$column = $this->columnHeaderStartColumn[0];
			$this->mergeCells($worksheetIndex, 8, $column, 9, $column);
			$column ++;
			$mergeColumns = count($this->params['results']) + 1;
			$element = json_decode($this->params['element']);
			foreach($element as $cycleId){
				$this->mergeCells($worksheetIndex, 8, $column, 8, $column + $mergeColumns - 1);
				$column += $mergeColumns;
			}
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
				if (strpos($key, '_result') !== false && isset($content[$key]) && strtolower($content[$key]) != 'pass'){
					$style[$key] = 'warning';
					if(strtolower($content[$key]) == 'n/t')	
						$style[$key] = 'nt';
				}
			}
		}
		return $style;
	}
	
	protected function postProcess($worksheetIndex = 0){
		if ($worksheetIndex == 0){
			if(isset($this->data[$worksheetIndex]['module'])){
				$passRates = array();
				$crCounts = array();
				// write the total row
				$this->writeTotal($worksheetIndex);
				
				$this->nextRow[0] ++;
				// write the rate row
				$this->writeRate($worksheetIndex);
				
				// write the statistics data
				$this->writeStatistics($worksheetIndex);
			}
		}
	}
	
	private function writeTotal($worksheetIndex = 0){
		$column = $this->columnHeaderStartColumn[$worksheetIndex];
		foreach($this->columnHeaders[0]['rows'][0] as $key=>$title){
			if ($key == 0)
				$this->writeCell($worksheetIndex, $this->nextRow[0], $column, 'Total:', 'total');
			else{
				if ($title['label'] != 'CRID'){
					$beginLetter = $this->RC2LN($this->columnHeaderRow[0] + 1, $column);// chr($beginIndex);
					$lastLetter = $this->RC2LN($this->nextRow[0] - 1, $column);// chr($beginIndex);
					
					$formula = '=SUM('.$beginLetter.':'.$lastLetter.')';
					$this->writeCell($worksheetIndex, $this->nextRow[0], $column, $formula, 'total');
				}
				else{
					// get the count of crs
					$crs = 0;
					$allCr = array();
					foreach($this->data[0]['module'] as $module_id=>$md){
						foreach($md as $k=>$v){
							if (isset($md[$title['index']])){
								$allCr[] = $md[$title['index']];
							}
						}
					}					
					// get the cycleid from the key
					$cycleId = '0';
					if (preg_match("/^(\d*)_crid$/", $title['index'], $matches)){
						$cycleId = $matches[1];
					}
					$this->data[2][0][$cycleId] = 0;
					$this->data[2][1][$cycleId] = 0;
					if (!empty($allCr)){	//取出该cycle中所有的cr？
						$tmp = implode(',', $allCr);
						$tmp = array_unique(explode(",", $tmp));
//print_r($tmp);						
						$crs = count($tmp);
						$this->data[2][1][$cycleId] = $crs;
					}
					$this->writeCell($worksheetIndex, $this->nextRow[0], $column, $crs, 'total');
				}
			}
			$column ++;
		}
	}
	
	private function writeRate($worksheetIndex = 0){
		$column = $this->columnHeaderStartColumn[$worksheetIndex];
		foreach($this->columnHeaders[0]['rows'][0] as $key=>$title){
			if ($key == 0)
				$this->writeCell($worksheetIndex, $this->nextRow[0], $column, 'Rate:', 'total');
			else{
				if ($title['label'] != 'CRID'){
					$lastLetter = $this->RC2LN($this->nextRow[0] - 1, $column);// chr($beginIndex);
					$formula = 0.00;
					if($title['cases'])
						$formula = '='.$lastLetter.'/'.$title['cases'];//.$this->data[0]['cycle'][]['Cases'];
					$this->writeCell($worksheetIndex, $this->nextRow[0], $column, $formula, 'percent');
					if ($title['label'] == 'Pass'){
						$cycleId = '0';
						if (preg_match("/^(\d*)_pass$/", $title['index'], $matches)){
							$cycleId = $matches[1];
						}
						$LN = $this->RC2LN($this->nextRow[0], $column);
						$this->data[2][2][$cycleId] = $this->objExcel->getActiveSheet()->getCell($LN)->getCalculatedValue();
						$this->data[2][2][$cycleId] = $this->data[2][2][$cycleId]*100;
						$n = stripos($this->data[2][2][$cycleId], '.');
						$this->data[2][2][$cycleId] = substr($this->data[2][2][$cycleId], 0, $n+3)."%";
					}
				}
				else
					$this->writeCell($worksheetIndex, $this->nextRow[0], $column, '', 'total');
			}
			$column ++;
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
	
	
	private function writeStatistics($worksheetIndex = 0){
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
		$result = array();
		$strCycleIds = implode(',', json_decode($this->params['element']));

		if ($worksheetIndex == 1){
			$sql = "SELECT `name` as `module`, `element_id` as `codec_stream_ids` FROM `tag` WHERE table='xt.codec_stream'";
			$res = $this->tool->query($sql);
			while($data = $res->fetch()){
				$r_sql = "SELECT detail.codec_stream_id as codec_stream_id, codec_stream.name as codec_stream,".
					" detail.test_env_id as test_env_id, test_env.name as test_env,".
					" detail.testcase_id as action_id, testcase.code as action,".
					" detail.result_type_id as result_type_id, result_type.name as result_type,".
					" detail.comment as comment, detail.defect_ids as crid,".
					" last_result.result_type_id as result_type_id, detail.defect_ids as crid".
					" FROM cycle_detail detail".
					" LEFT JOIN codec_stream ON codec_stream.id=detail.codec_stream_id".
					" LEFT JOIN test_env ON test_env.id=detail.test_env_id".
					" LEFT JOIN testcase ON testcase.id=detail.testcase_id".
					" LEFT JOIN result_type ON result_type.id=detail.result_type_id".
					" WHERE detail.cycle_id IN ($strCycleIds)".
					" AND detail.codec_stream_id IN ({$data['element_id']})".
					" ORDER BY detail.codec_stream_id ASC";				
				$r_res = $this->params['db']->query($r_sql);
				while($row = $res->fetch()){
					$keyIndex = $row['codec_stream_id'];
					if(isset($this->data[1][$keyIndex]['module'])){ 
						if($this->data[1][$keyIndex]['module'] != $data['module'])
							$keyIndex .= "_1";
					}
					else
						$this->data[1][$keyIndex]['module'] = $data['module'];
					if(!isset($this->data[1][$keyIndex]['codec_stream']))
						$this->data[1][$keyIndex]['codec_stream'] = $data['codec_stream'];
					if(!isset($this->data[1][$keyIndex]['test_env']))
						$this->data[1][$keyIndex]['test_env'] = $data['test_env'];
					$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['action'] = $data['action'];
					if($row['result_type_id'] == 0)
					$this->data[1][$row['tc_id']][$row['cycle_id'].'_result'] = 'blank';
				
					if(isset($this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['last_result'])){
						if($this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['finish_time'] < $row['finish_time']){
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['last_result'] = $row['result_type'];
							if($row['result_type_id'] == 0)
								$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['last_result'] = 'blank';
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['finish_time'] = $row['finish_time'];
						}
					}else{
						$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['last_result'] = $row['result_type'];
						if($row['result_type_id'] == 0)
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['last_result'] = 'blank';
						$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['finish_time'] = $row['finish_time'];
					}
					
					if (!empty($row['defect_ids'])){
						if (empty($this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['defect_ids']))
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['defect_ids'] = "";
						else
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['defect_ids'] .= "\n";
						$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['defect_ids'] .= $row['cycle'].":".$row['defect_ids'];
					}
					if (!empty($row['comment'])){
						if (empty($this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['comment']))
							$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['comment'] = "";
						$this->data[1][$keyIndex][$data['test_env_id']][$data['action_id']]['comment'] .= $row['cycle'].":".$row['comment']."\n";
					}
					
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
