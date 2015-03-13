<?php
/*
从一个Codec的Excel文件中导入数据，包括Case数据、Stream数据、Trick数据和测试结果数据
*/

require_once('importer_testcase.php');

class xt_zzvw_cycle_importer_mqx extends importer_testcase{
	private $root;
	private $root_info = array();
	private $defect_list = array();
	private $config = array();
	private $reader = null;
	private $writer = null;
	private $test_result = array();
	private $cover = array();
	protected $db;
	protected $data;
	protected $os;
	protected $os_id;
	protected $creater;
	protected $creater_id;
	
	public function setOptions($jqgrid_action){
		$this->testcase_type = 'MQX';
	}
	
	private function handleZipFile($fileName){
		$fName = dirname($fileName).'/'.basename($fileName, '.zip');

		if(!file_exists($fName)){
			if(PHP_OS == 'Linux'){
				$cmd = 'unzip -o '.$fileName.' -d '.dirname($fileName);
				$line = exec($cmd, $output, $retVal);	
				if ($retVal){ // failed
						// print_r("retVal = $retVal");
						return;
					}					
				$cmd = "gunzip -S .zip '".$fileName."'";
				system($cmd, $retValue);
			}
			else if(PHP_OS == 'WINNT'){
				$cmd = 'wzunzip '.$fileName.' '.dirname($fileName).' -d';
				$line = exec($cmd, $output, $retVal);
				if ($retVal){ // failed
					// print_r("retVal = $retVal");
					return;
				}
			}
		}
// print_r($fName);
		return $fName;
	}
	
	protected function _import($fileName){
print_r($this->params);
return;
		$baseName = basename($fileName);//只要zip????
		if (stripos(strtolower($baseName), '.zip') !== false){
			$fileName = $this->handleZipFile($fileName);
		}
		$this->root($fileName);
		$this->data = $this->parse($fileName);;
		$this->os = 'mqx';
		$this->os_id = $this->getElementId('os', array('name'=>$this->os));
		$this->creater = 'b46350';
		$this->creater_id = $this->getElementId('useradmin.users', array('username'=>$this->creater));
		return $this->process();
	}
	
	public function parse($fileName){
		$dirs = scandir($this->root);
		foreach($dirs as $dir){
			if ($dir == '.' || $dir == '..')
				continue;
			if (stripos($dir, 'defect_list') !== false){
				$this->ana_defect_list($dir);	
//print_r($this->defect_list);				
			}
			else
				$this->ana_dir($dir);
//print_r($this->test_result);		
		}
		return array('test_result'=>$this->test_result, 'cover'=>$this->cover);
	}
	
	public function generateCycleName($cycleInfo){
		$start_date = strtotime($cycleInfo['start_date']);
		$currentYear = (int)date('y', $start_date);
		$currentWorkWeek = (int)date('W', $start_date);
		$week = sprintf("%2dWK%02d", $currentYear, $currentWorkWeek);
		if (empty($cycleInfo['myName'])){
			$cycleInfo['myName'] = date('Ymd', $start_date);
		}
// print_r($cycleInfo);		
		return $cycleInfo['prj'].'-'.$cycleInfo['cycle_type'].'-'.$week.'-'.$cycleInfo['myName'];
	}
	
	public function createCycle($cycleInfo, &$cycleName){//$prj, $rel, $target = 'Release', $compiler = 'GCC', $cycle_type = 'Fun', $week = '', $myName = ''){
		if (!isset($cycleInfo['prj_id']))
			$cycleInfo['prj_id'] = $this->getElementId('prj', array('name'=>$cycleInfo['prj']));
		if (!isset($cycleInfo['rel_id']))
			$cycleInfo['rel_id'] = $this->getElementId('rel', array('name'=>$cycleInfo['rel']));
		if (!isset($cycleInfo['compiler_id'])){
			if (!isset($cycleInfo['compiler']))
				$cycleInfo['compiler'] = 'GCC';
			$cycleInfo['compiler_id'] = $this->getElementId('compiler', array('name'=>$cycleInfo['compiler']));
		}
		if (!isset($cycleInfo['cycle_type_id'])){
			if (!isset($cycleInfo['cycle_type']))
				$cycleInfo['cycle_type'] = 'Fun';
			$cycleInfo['cycle_type_id'] = $this->getElementId('cycle_type', array('name'=>$cycleInfo['cycle_type']));
		}
		if (!isset($cycleInfo['build_target_id'])){
			if (!isset($cycleInfo['build_target']))
				$cycleInfo['build_target'] = 'Release';
			$cycleInfo['build_target_id'] = $this->getElementId('build_target', array('name'=>$cycleInfo['build_target']));
		}
		if (!isset($cycleInfo['cycle_type']))
			$cycleInfo['cycle_type'] = 'Fun';
		if (empty($cycleInfo['start_date']))
			$cycleInfo['start_date'] = date('Y-m-d');
		if (!isset($cycleInfo['name'])){
//			if (!isset($cycleInfo['myName']))
//				$cycleInfo['myName'] = $cycleInfo['compiler'].'_'.$cycleInfo['build_target'].'_'.date('Ymd');
			$cycleInfo['name'] = $cycleName = $this->generateCycleName($cycleInfo);//['prj'], $cycleInfo['cycle_type'], $cycleInfo['start_date'], $cycleInfo['myName']);
		}
//print_r($cycleInfo);		
		$new = false;
		return $this->getElementId('cycle', $cycleInfo, $new);
	}
	
	public function getCaseInfo($case){
		if (!isset($case['testcase_testpoint']) && isset($case['testcase_module']))
			$case['testcase_testpoint'] = 'Default testpoint for '.$case['testcase_module'];
		$testcase = array('code'=>$case['code']);
		if (isset($case['summary']))
			$testcase['summary'] = $case['summary'];
		else
			$testcase['summary'] = 'Summary for '.$case['code'];

		$transfer_fields = array('testcase_type'=>'Linux BSP', 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 'group'=>'');
		foreach($transfer_fields as $field=>$defaultValue){
			$hasDefaultValue = true;
			if (is_int($field)){
				$field = $defaultValue;
				$hasDefaultValue = false;
			}
			if(isset($case[$field.'_id']))
				$testcase[$field.'_id'] = $case[$field.'_id'];
			else{
				if(!isset($case[$field]) && $hasDefaultValue)
					$case[$field] = $defaultValue;
				if (isset($case[$field])){
					$v = array('name'=>$case[$field]);
					if ($field == 'testcase_module')
						$v['testcase_type_ids'] = $testcase['testcase_type_id'];
					elseif ($field == 'testcase_testpoint')
						$v['testcase_module_id'] = $testcase['testcase_module_id'];
					$testcase[$field.'_id'] = $this->getElementId($field, $v);
				}
			}
		}
		$newCase = false;
		$case_id = $this->getElementId('testcase', $testcase, array('code'), $newCase);
		
		$version = array('testcase_id'=>$case_id, 'edit_status_id'=>EDIT_STATUS_PUBLISHED);
		$fields = array('testcase_priority_id'=>3, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'command'=>'', 'objective'=>'', 'precondition'=>'', 
			'steps'=>'', 'expected_result'=>'', 
			'parse_rule_id'=>1, 'parse_rule_content'=>'', 'auto_run_seconds'=>0, 'manual_run_seconds'=>0,
			//'created'=>date('Y-m-d H:i:s'), 'update_comment'=>'', 'review_comment'=>''
			);
		foreach($fields as $field=>$defaultValue){
			$version[$field] = isset($case[$field]) ? $case[$field] : $defaultValue;
		}
		$newVer = false;
		$version_id = $this->getElementId('testcase_ver', $version, array(), $newVer);
		if ($newVer && !$newCase){
			$res = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->db->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		}
		return array('testcase_id'=>$case_id, 'testcase_ver_id'=>$version_id);
	}	
	
	public function generateProjectName($chip, $board_type, $os){
		return $chip.'-'.$board_type.'-'.$os;
	}
	protected function getResultId($result){
		$result = strtolower($result);
			
		switch(strtolower($result)){
			case 'ok':
			case 'pass':
				$result = 'pass';
				break;
			case 'fail':
			case 'nok':
				$result = 'fail';
				break;
			case 'na':
			case 'n/a':
				$result = 'N/A';
				break;
			case 'nt':
			case 'n/t':
				$result = 'N/T';
				break;
			case 'ns':
			case 'n/s':
				$result = 'N/S';
				break;
		}
		if (stripos($result, 'not support') !== false)
			$result = 'N/S';
		
		return $this->getElementId('result_type', array('name'=>$result));
	}
	
	protected function getChipTypeId($chipName){
		$chip_type_id = 0;
		$os_mqx = $this->getElementId('os', array('name'=>'mqx'));
		$board_type_twr = $this->getElementId('board_type', array('name'=>'twr'));
		$board_type_autoevb = $this->getElementId('board_type', array('name'=>'autoevb'));
		if (preg_match('/^(i\.mx|mx|m[^x]|vf|px|k)(.*)/i', $chipName, $matches)){
//print_r($matches);		
			switch(strtolower($matches[1])){
				case 'i.mx':
				case 'mx':
					$chip_type = array('name'=>'MX'.$matches[2][0], 'os_ids'=>'1,2,3,4,5,6,7', 'board_type_ids'=>'1,2,3,4,5,6,7,8,9,10,11,12,13,14');
					break;
				case 'vf':
					$chip_type = array('name'=>'Vybrid', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				case 'px':
					$chip_type = array('name'=>'PowerPC', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				case 'k':
					$chip_type = array('name'=>'Kinetis', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				default: // m?
					$chip_type = array('name'=>'ColdFire', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
			}
			$chip_type_id = $this->getElementId('chip_type', $chip_type);
		}
//print_r("chipname = $chipName, type_id = $chip_type_id\n");		
		return $chip_type_id;
	}
	
	public function getChipId($chipName){
		$chip_type_id = $this->getChipTypeId($chipName);
		return $this->getElementId('chip', array('name'=>$chipName, 'chip_type_id'=>$chip_type_id));
	}
	
	function excelTime($date, $time = false) {
		if (function_exists('GregorianToJD')) {
			if (is_numeric($date)) {
				$jd = GregorianToJD(1, 1, 1970);
				$gregorian = JDToGregorian($jd + intval($date) - 25569);
				$date = explode('/', $gregorian);
				$date_str = str_pad($date[2], 4, '0', STR_PAD_LEFT) . "-" . str_pad($date[0], 2, '0', STR_PAD_LEFT) . "-" . str_pad($date[1], 2, '0', STR_PAD_LEFT) . ($time ? " 00:00:00" : '');
				return $date_str;
			}
		} else {
				$date = $date > 25568 ? $date + 1 : 25569;
				/*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
				$ofs = (70 * 365 + 17 + 2) * 86400;
				$date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
		}
		return $date;
	}
	
	protected function process(){
print_r($this->params);
		$cycle_ids = array();
		foreach($this->data['test_result'] as $sourceName=>$v){
			$case = array('code'=>$sourceName, 'testcase_type'=>'MQX', 'group_id'=>'6', 'summary'=>$v['Sourse Name'], 'testcase_module'=>$v['sheet'], 'steps'=>$v['Test Steps'], 
				'precondition'=>$v['precondition'], 'testcase_priority_id'=>$v['testcase_priority'], 'expected_result'=>$v['Expected result'], 'isactive'=>ISACTIVE_ACTIVE);
			$caseInfo = $this->getCaseInfo($case, TESTCASE_TYPE_MQX);
			foreach($v['build_target'] as $build_target=>$boardResult){
				$build_target_id = $this->getElementId('build_target', array('name'=>$build_target));
				foreach($boardResult as $board=>$v2){
					// generate the project
					if (preg_match('/^(.*)-(.*)$/', $board, $matches)){
						$board_type = $matches[1];
						$chip = $matches[2];
						$prj_name = $this->generateProjectName($chip, $board_type, $this->os);
						$board_type_id = $this->getElementId('board_type', array('name'=>$board_type));
						$chip_type_id = $this->getChipTypeId($chip);
						
						//处理chip
						$chip_id = $this->getElementId('chip', array('name'=>$chip, 'chip_type_id'=>$chip_type_id));
						
						$prj_id = $this->getElementId('prj', array('name'=>$prj_name, 'board_type_id'=>$board_type_id, 'chip_id'=>$chip_id, 'os_id'=>$this->os_id));
						
						// prj_testcase_ver
						$res = $this->db->query("SELECT * FROM testcase_ver WHERE id={$caseInfo['testcase_ver_id']}");
						$ver = $res->fetch();
						$prj_testcase_ver = array('prj_id'=>$prj_id, 'testcase_id'=>$caseInfo['testcase_id'], 'testcase_ver_id'=>$caseInfo['testcase_ver_id'], 
							'owner_id'=>$ver['owner_id'], 'testcase_priority_id'=>$ver['testcase_priority_id'], 'auto_level_id'=>$ver['auto_level_id'], 
							'edit_status_id'=>$ver['edit_status_id']);
						$this->getElementId('prj_testcase_ver', $prj_testcase_ver);
						
					}
					else{
						// print_r("board = $board\n");
						break;
					}
					foreach($v2 as $compiler=>$v3){
						$compiler_id = $this->getElementId('compiler', array('name'=>$compiler, 'os_id'=>$this->os_id));
						foreach($v3 as $rel=>$result){
//print_r($result);						
							// generate the release
							$rel_id = $this->getElementId('rel', array('name'=>$rel));
							$cycleInfo = compact('prj_id', 'rel_id', 'compiler_id', 'compiler', 'build_target', 'build_target_id');
							$cycleInfo['testcase_type_id'] = TESTCASE_TYPE_MQX;
							$cycleInfo['prj'] = $prj_name;
							$cycleInfo['creater_id'] = $cycleInfo['tester_ids'] = $this->creater_id;
							$cycleInfo['start_date'] = $this->excelTime($result['start_date']);
							$cycleInfo['end_date'] = $this->excelTime($result['end_date']);
							
							$cycleName = '';
							$cycle_id = $this->createCycle($cycleInfo, $cycleName);
print_r($cycle_id."\n");
							$cycle_ids[] = $cycle_id;
							$build_result = strtolower($result['Build result']);
							$test_result = strtolower($result['Result']);
							$defect_ids = $result['Tracking No'];
							$finish_time = $this->excelTime($result['date']);
							$cycle_detail = array('cycle_id'=>$cycle_id, 'testcase_id'=>$caseInfo['testcase_id'], 'testcase_ver_id'=>$caseInfo['testcase_ver_id'], 
								'build_result_type_id'=>$this->getResultId($build_result), 'result_type_id'=>$this->getResultId($test_result),
								'defect_ids'=>$defect_ids, 'issue_comment'=>$result['Status of bug'], 'finish_time'=>$finish_time
								);
							$cycle_detail_id = $this->getElementId('cycle_detail', $cycle_detail);
							if (!empty($result['logfile'])){
								$fileName = basename($result['logfile']);
								$dest = $this->formatFileName(LOG_ROOT.'/'.$cycleName.'_'.$cycle_id.'/'.$sourceName.'_'.$cycle_detail_id.'/'.$fileName, '.txt');
//								$dest = $this->moveFile($result['logfile'], $dest);
//print_r($dest);
								
//								$this->createDirectory($dest);
								if (!copy($result['logfile'], $dest))
									print_r(">>>>>Failed to copy dest = $dest, fileName = $fileName\n")								;
									
							}
							// 更新testcase_last_result
							$res = $this->db->query("select * from testcase_last_result WHERE testcase_id={$caseInfo['testcase_id']} and rel_id={$rel_id} and prj_id={$prj_id}");
							if ($row = $res->fetch()){
								if ($row['tested'] < $result['date']){
									$this->db->update('testcase_last_result', array('cycle_detail_id'=>$cycle_detail_id, 'result_type_id'=>$cycle_detail['result_type_id'], 'tested'=>$finish_time), "id=".$row['id']);
								}
							}
							else{
								$this->db->insert('testcase_last_result', array('testcase_id'=>$caseInfo['testcase_id'], 'rel_id'=>$rel_id, 'prj_id'=>$prj_id, 'cycle_detail_id'=>$cycle_detail_id, 'result_type_id'=>$cycle_detail['result_type_id'], 'tested'=>$finish_time));
							}
							// 更新testcase.last_run
							$res = $this->db->query("select * from testcase where id={$caseInfo['testcase_id']}");
							$row = $res->fetch();
//print_r("finish_time = $finish_time, last_run={$row['last_run']}, case_id={$caseInfo['testcase_id']}\n");
							if ($row['last_run'] < $result['date']){
								$this->db->update('testcase', array('last_run'=>$finish_time), 'id='.$caseInfo['testcase_id']);
							}
						}
					}
				}
			}
		}
	}
	
	public function root($root){
		$this->root = $root;
		$path_info = pathinfo($this->root);
		$this->root_info = $this->ana_root($path_info['basename']);
// print_r($this->root_info);		
	}
	
	
	
	public function root_info(){
		return $this->root_info;
	}
	
	private function readExcel($fileName){
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($fileName);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
		$objExcel = $reader->load($fileName);
		return $objExcel;
	}
	
	private function ana_root($root){
		$pattern = isset($this->congif['root_pattern']) ? $this->congif['root_pattern'] : '/(.*?)_MQX_(.*)$/i';
		$matches = array();
		preg_match($pattern, $root, $matches);
		return $matches;
	}

	private function ana_defect_list($dir){
		$files = scandir($this->root.'/'.$dir);
		foreach($files as $file){
			if ($file == '.' || $file == '..')
				continue;
			$objExcel = $this->readExcel($this->root.'/'.$dir.'/'.$file);
			$ignore = array('cover', 'ibm rational clearquest web');
			foreach($objExcel->getWorksheetIterator() as $index=>$sheet){
				$title = strtolower($sheet->getTitle());
				if (in_array($title, $ignore))continue;
				$b_c = preg_split('/[\s-]+/', $title);
//print_r("title = $title, ");				
//print_r($b_c);
				// if the last component is numeric, the it should attach to the previous one as the compiler
				$cc = count($b_c);
				if ($cc >= 2){
					if (is_numeric($b_c[$cc - 1])){
						$compiler = $b_c[$cc - 2].$b_c[$cc - 1];
						array_pop($b_c);
					}
					else
						$compiler = $b_c[$cc - 1];
					array_pop($b_c);
					$board = implode('', $b_c);
//print_r("title = $title, board = $board, compiler = $compiler\n");					
				}
				else{
					die("ERROR, THE TITLE IS $title");
				}
//print_r("board = $board, compiler = $compiler\n");				
				foreach ($sheet->getRowIterator() as $i=>$row) {
					if ($i == 1)continue;
					$sheetName = '';
					$sourceName = '';
					$cellIterator = $row->getCellIterator();
					foreach ($cellIterator as $n=>$cell) {
						switch($n){
							case 1: // source name
								$cellV = trim($cell->getValue());
//print_r($cellV);							
								$lines = preg_split("/[\n\r]+/", $cellV);
								
//print_r($lines);
								// line1 is the module and line2 is the source name
								$sheetName = $lines[0];
								if (isset($lines[1])){
									$sourceName = $lines[1];
									if ($sourceName[0] == '(')
										$sourceName = substr($sourceName, 1, -1);
								}
								else{ // it shoule be 'All xxx examples'
//print_r($lines[0]);
									$ws = explode(' ', $lines[0]);
									if (count($ws) > 1)
										$sourceName = 'all-'.strtolower($ws[1]);
									else
										$sourceName = $lines[0];
								}
								break;
							case 2: // FSL tracking number
								if (!empty($sourceName))
									$this->defect_list[strtolower($board)][strtolower($compiler)][strtolower($sourceName)] = trim($cell->getValue());
								break;
						}
					}
				}
//print_r($this->defect_list);				
			}
			$objExcel->disconnectWorksheets();
			unset($objExcel);
		}
	}
	
	public function ana_dir($dir){
	//print_r($dir);
		$pattern = isset($this->congif['dir_pattern']) ? $this->congif['dir_pattern'] : '/^(.*)-(.*?)$/i';//'/^report_TWR-?(.*)_(.*?)$/i';
		if (preg_match($pattern, $dir, $matches)){
//	print_r($matches);		
			$board = strtolower($matches[1]);
			$compiler = strtolower($matches[2]);
			//$this->test_result[$board][$compiler] = 
			$this->ana_cycle($dir, $board, $compiler);
		}
	}

	private function ana_cycle($root, $board, $compiler){
		$cycle = array();
		$files = scandir($this->root.'/'.$root.'/TEST REPORT');
		foreach($files as $file){
			if ($file != '.' && $file != '..'){
				$cycle = $this->ana_excel($root, 'TEST REPORT', $file, $board, $compiler);
			}
		}
//print_r($cycle);		
		return $cycle;
	}

	private function ana_excel($root, $dir, $file, $board, $compiler){
//print_r($root);
//	print_r($file);
		$fileName = $this->root.'/'.$root.'/'.$dir.'/'.$file;
		$objExcel = $this->readExcel($fileName);
		$ignore = array('test case list', 'test report');
		$cycle = array();
		$titles = array();
		$build_target = 'Release';
		foreach($objExcel->getWorksheetIterator() as $index=>$sheet){
			$title = strtolower($sheet->getTitle());
	//		print_r($title);
			if (in_array($title, $ignore))continue;
			if ($title == 'cover'){
				// get the cycle start time
				$g6 = $sheet->getCell('G6')->getValue(); // start date
				$g7 = $sheet->getCell('G7')->getValue(); // end date
				$cycle['start_date'] = $g6;
				$cycle['end_date'] = $g7;
				// get the build target
				//先从B19开始读取，一直往后，知道取得Scope,读取Scope后的第二行
				$row = 19;
				$found = false;
				do{
					$scope = $sheet->getCell('B'.$row)->getValue();
					if ($scope == 'Scope'){
						$found = true;
						break;
					}
					$row ++;
				}while($row < 100);
				if ($found){
					$scope = $sheet->getCell('B'.($row + 2))->getValue();
					$words = explode(',', $scope);
					$build_target = preg_replace(array('/ target$/i', '/ /'), array('', '_'), trim(array_pop($words)));
				}
				else
					$build_target = 'Release';
				
				continue;
			}
			foreach ($sheet->getRowIterator() as $i=>$row) {
				if ($i < 7)continue;
//				if ($title != 'Demo' && $i == 8)
//					continue;
				$cellIterator = $row->getCellIterator();
				foreach ($cellIterator as $n=>$cell) {
					$v = trim($cell->getValue());
					if ($i == 7){
						if (!empty($v)){
							$v = str_replace("\n", '', $v);
							$titles[$n] = strtolower($v);
							if ($titles[$n] == 'id'){
								$source_name_column = $n;
							}
							
							if ($titles[$n] == 'sourse name' || $titles[$n] == 'sourse ongoingme'){
//								$source_name_column = $n;
								$titles[$n] = 'sourse name';
							}
							
						}
					}
					else{
//						if ($n < $source_name_column)continue;
						if ($n == $source_name_column) {
							$source_name = $v;
//							print_r("file = $file, title = $title, source_name_column = $source_name_column, source_name = $source_name\n");						
						}
						if (empty($titles[$n]))
							continue;
						if ($titles[$n] == 'actual result'){
							if ($cell->hasHyperlink()){ // 需要取出索引的文件
								print_r("url = ".$cell->getHyperLink()->getUrl().", tooltip=".$cell->getHyperLink()->getTooltip());
							}
						}
//print_r("file = $file, title = $title, n = $n, titles[n] = {$titles[$n]}, source_name_column = $source_name_column, i = $i, source_name = $source_name\n");						
						$cycle[$source_name][$titles[$n]] = $v;
					}
				}
				if ($i == 7)
					continue;
//print_r($cycle[$source_name]);

				if (strtoupper($cycle[$source_name]['result']) == 'FAIL'){ // check if there're FSL tracking number
					$track_num = $this->getTrackNumber($board, $compiler, $source_name, $title);
//print_r("source_name = $source_name, board = $board, comp = $compiler, tracknum = $track_num\n");
					if (!empty($track_num))
						$cycle[$source_name]['freescale tracking number'] = $track_num;
				}
				$this->test_result[$source_name]['code'] = isset($cycle[$source_name]['id']) ? $cycle[$source_name]['id'] : '';
				$this->test_result[$source_name]['testcase_priority'] = isset($cycle[$source_name]['priority']) ? $cycle[$source_name]['priority'] : 3;
				$this->test_result[$source_name]['Sourse Name'] = $cycle[$source_name]['sourse name'];
				$this->test_result[$source_name]['Expected result'] = isset($cycle[$source_name]['expected result']) ? $cycle[$source_name]['expected result'] : (isset($cycle[$source_name]['expected results']) ? $cycle[$source_name]['expected results'] : '');
				$this->test_result[$source_name]['sheet'] = $title;
				$this->test_result[$source_name]['Test Steps'] = isset($cycle[$source_name]['test steps']) ? $cycle[$source_name]['test steps'] : '';
				$this->test_result[$source_name]['precondition'] = isset($cycle[$source_name]['preconditions']) ? $cycle[$source_name]['preconditions'] : '';
				

				$this->test_result[$source_name]['build_target'][$build_target][$board][$compiler][$this->root_info[2]] = array(
					'start_date'=>$cycle['start_date'],
					'end_date'=>$cycle['end_date'],
					'Tuning description'=>isset($cycle[$source_name]['tuning description']) ? $cycle[$source_name]['tuning description'] : (isset($cycle[$source_name]['memory tuning description']) ? $cycle[$source_name]['memory tuning description'] : ''),
					'Build result'=>isset($cycle[$source_name]['build result(without tuning)']) ? $cycle[$source_name]['build result(without tuning)'] : (isset($cycle[$source_name]['build result (without tuning)']) ? $cycle[$source_name]['build result (without tuning)'] : ''),
					'Actual result'=>isset($cycle[$source_name]['actual result']) ? $cycle[$source_name]['actual result'] : '',
					'Result'=>isset($cycle[$source_name]['result']) ? $cycle[$source_name]['result'] : '',
					'Tracking No'=>isset($cycle[$source_name]['cr id']) ? $cycle[$source_name]['cr id'] : (isset($cycle[$source_name]['freescale tracking number']) ? $cycle[$source_name]['freescale tracking number'] : ''),
					'Status of bug'=>isset($cycle[$source_name]['status of bug']) ? $cycle[$source_name]['status of bug'] : '',
					'date'=>isset($cycle[$source_name]['date']) ? $cycle[$source_name]['date'] : ''
				);
				// 处理logfile，因hyperlin无法读出来，只能用文件名直接匹配来进行
				$module = $title;
				if (preg_match('/^(.*?)_examples$/', $title, $matches)){
					$module = $matches[1];
				}
				$fileName = $cycle[$source_name]['sourse name'];
				// 如果存在'/'，则取/后面部分
				if (preg_match('/^.*\\\(.*)$/', $fileName, $matches)){
//print_r(">>>>>>>>>>>>$fileName, {$matches[1]}<<<<<<<<<<<<<<<<\n");				
					$fileName = $matches[1];
				}
				$logFile = $this->root.'/'.$root.'/'.strtoupper($root).'/'.strtoupper($module).'/'.$fileName.'.txt';
				if (file_exists($logFile)){
					$this->test_result[$source_name]['build_target'][$build_target][$board][$compiler][$this->root_info[2]]['logfile'] = $logFile;
				}
				else{
//print_r(">>>>>the logfile = $logFile does not exist\n");
				}
				
				if (!isset($this->cover[$board][$compiler][$this->root_info[2].'_total']))
					$this->cover[$board][$compiler][$this->root_info[2].'_total'] = 0;
				$this->cover[$board][$compiler][$this->root_info[2].'_total'] ++;
				if (!isset($this->cover[$board][$compiler][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])]))
					$this->cover[$board][$compiler][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])] = 0;
				$this->cover[$board][$compiler][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])] ++;
				
				if (!isset($this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_total']))
					$this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_total'] = 0;
				$this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_total'] ++;
				if (!isset($this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])]))
					$this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])] = 0;
				$this->cover[$board][$compiler]['module'][$title][$this->root_info[2].'_'.strtolower($cycle[$source_name]['result'])] ++;
				
				if (!empty($track_num)){
//print_r("$board, $compiler, $title, {$this->root_info[2]}, $track_num\n");
					$this->cover[$board][$compiler]['module'][$title][$this->root_info[2]]['tracknumber'][$track_num] = $track_num;
					$this->cover[$board][$compiler][$this->root_info[2]]['tracknumber'][$track_num] = $track_num;
				}
			}
		}
//print_r($this->test_result);
		$objExcel->disconnectWorksheets();
		unset($objExcel);
		return $cycle;
	}
	
	private function getTrackNumber($board, $compiler, $source_name, $module_name = ''){
		$track_num = '';
		$board = strtolower($board);
		$compiler = strtolower($compiler);
		$source_name = strtolower($source_name);
//print_r("board = $board, ");
//		$board = preg_replace('/[\s_]/', '', $board);
//print_r("board = $board>>>>>>>>\n");		
		$b = $board;
		$c = $compiler;
		$try_method = 'c';
		$last = substr($board, -1, 1);
		while(!isset($this->defect_list[$b][$c]) && $try_method != 'e'){
			switch($try_method){
				case 'c':
					if ($c == 'cw10')
						$c = 'cw10.2';
					$try_method = '0';
					break;
				case '0':
					if (substr($compiler, -1) == '0')
						$c = substr($compiler, 0, -1);
					$try_method = 'b';
					break;
				case 'b':
					if ($last == 'm')
						$b = substr($board, 0, -1);
					else
						$b = $board.'m';
					$try_method = 'bc';
					break;
				case 'bc':
					if ($c == 'cw10')
						$c = 'cw10.2';
					if ($last == 'm')
						$b = substr($board, 0, -1);
					else
						$b = $board.'m';
					$try_method = 'p';
					break;
				default:
					$try_method = 'e';
			}
		}
//print_r("try_method = $try_method\n");
		if ($try_method != 'e')
			$track_num = isset($this->defect_list[$b][$c][$source_name]) ? $this->defect_list[$b][$c][$source_name] : '';
		if(empty($track_num)){ // try the ALL-module_name
			$ws = preg_split('/[_ ]+/', $module_name);
			$all_module = 'all-'.strtolower($ws[0]);
			if (isset($this->defect_list[$b][$c][$all_module]))
				$track_num = $this->defect_list[$b][$c][$all_module];
//print_r(array($module_name, $all_module, $track_num));
//print_r($this->defect_list[$b][$c]);
		}	
		return $track_num;
	}
	
};


?>