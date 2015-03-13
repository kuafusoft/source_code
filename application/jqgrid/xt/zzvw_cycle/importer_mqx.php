<?php
/*
从一个Codec的Excel文件中导入数据，包括Case数据、Stream数据、Trick数据和测试结果数据
*/

require_once('importer_testcase.php');

class xt_zzvw_cycle_importer_mqx extends importer_testcase{
	private $root;
	private $defect_list = array();
	private $reader = null;
	private $writer = null;
	private $info;
	
	protected function init($params){
		importer_base::init($params);
	}
	
	public function setOptions($jqgrid_action){
		$this->testcase_type = 'MQX';
	}
	
	private function handleZipFile($fileName){
		$fName = dirname($fileName).'/'.basename($fileName, ".zip");
		if(file_exists($fName))
			return $fName;
		$this->tool->createDirectory($fName);
		if(PHP_OS == 'Linux'){
			$cmd = 'unzip -o '.$fileName.' -d '.$fName;
			$line = exec($cmd, $output, $retVal);	
			if ($retVal){ // failed
				print_r("retVal = $retVal");
				return;
			}
			// $cmd = "gunzip -S .zip '".$fileName."'";
			// system($cmd, $retValue);
		}
		else if(PHP_OS == 'WINNT'){
			$cmd = 'wzunzip -o '.$fileName.' '.$fName.' -d';
			$line = exec($cmd, $output, $retVal);
			if ($retVal){ // failed
				print_r("retVal = $retVal");
				return;
			}
		}
		if(file_exists($fName))
			return $fName;
	}
	
	protected function _import($fileName){
		$baseName = basename($fileName);//只要zip????
		if (stripos(strtolower($baseName), '.zip') !== false){
			$fileName = $this->handleZipFile($fileName);
		}
// return;
		$this->parseParentRoot($fileName);
		$dirs = scandir($this->root);
		foreach($dirs as $dir){
			if ($dir == '.' || $dir == '..')
				continue;
			if (stripos($dir, 'defect_list') !== false){
				$this->analyze_defect_list($dir);					
			}
			else{
print_r('parse + process:'."~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~"."\n<br />");
				$this->parse($dir);
// print_r($this->info);
// print_r("\n<br />");
				$this->process();
			}	
		}
print_r("\nNow you can close the dialog\n");		
	}
	
	public function parseParentRoot($root){
		$dirs = scandir($root);
		foreach($dirs as $dir){
			if($dir == "." || $dir == "..")
				continue;
			$this->root = $root."/".$dir;
			$this->analyze_root($dir);
		}
	}
	
	private function analyze_root($root){
		$pattern = isset($this->congif['root_pattern']) ? $this->congif['root_pattern'] : '/(.*?)_MQX_(.*)$/i';
		$matches = array();
		if(preg_match($pattern, $root, $matches)){
			$this->info['cycle']['upload_date'] = $matches[1];
			$this->info['cycle']['rel'] = $matches[2];
		}
		else
			return 'file name error';
	}
	
	public function parse($dir){
		$pattern = isset($this->congif['dir_pattern']) ? $this->congif['dir_pattern'] : '/^(.*)-(.*?)$/i';//'/^report_TWR-?(.*)_(.*?)$/i';
		if (preg_match($pattern, $dir, $matches)){
			$this->dir = $dir;
			$this->info[$dir]['platform'] = strtolower($matches[1]);
			$this->info[$dir]['os'] = 'mqx';
			$this->info[$dir]['compiler'] = strtolower($matches[2]);
			//$this->test_result[$board][$compiler] = 
			$this->parse_report($dir);
		}
		else 
			return 'file name error';
	}

	private function parse_report($root){
		$cycle = array();
		$test_report = $this->root.'/'.$root.'/TEST REPORT';
		if(file_exists($test_report)){
			$files = scandir($test_report);
			foreach($files as $file){
				if ($file != '.' && $file != '..'){
					$cycle = $this->parse_excel($root, 'TEST REPORT', $file);
				}
			}	
			return $cycle;
		}
	}
	
	private function parse_excel($root, $dir, $file){
		$fileName = $this->root.'/'.$root.'/'.$dir.'/'.$file;
		$inputFileType = PHPExcel_IOFactory::identify($fileName);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
		$objExcel = $reader->load($fileName);
		$this->analyze($objExcel);
	}
	
	public function analyze($excel){
		foreach($excel->getWorksheetIterator() as $index=>$sheet){
			$title = $sheet->getTitle();
			$ignore = array('test case list', 'test report');
			if (in_array(strtolower($title), $ignore))continue;
			$needParse = true;
			if (!empty($this->params['sheetsNeedParse']) && !in_array($title, $this->params['sheetsNeedParse']))
				$needParse = false;
//print_r("title = $title, need = $needParse\n");
			if ($needParse){
				$method = preg_replace('/[\s-=]/', '_', $title);
				$method = 'analyze_'.$method;
//print_r("title = $title, method = $method\n");				
				if (method_exists($this, $method)){
					$this->{$method}($sheet, $title);
				}
				else{
					$this->default_analyze_sheet($sheet, $title);
				}
			}
		}
		$excel->disconnectWorksheets();
		return $this->parse_result;
	}
	
	protected function default_analyze_sheet($sheet, $title){
		$highestRow = $sheet->getHighestRow(); // e.g. 10
		$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
 // print_r("title = $title, hiColumn = $highestColumn, highestRow = $highestRow\n");		
		$cm = $this->getColumnMap($title, $highestColumn);
		if (!empty($cm)){
			$isDataStart = false;
			for($row = $cm['start_row']; $row <= $highestRow; $row ++){
				foreach($cm['columns'] as $key=>$col){
					if($key != 'A'){
						if($isDataStart)
							$this->parse_result[$title][$row][$header[$key]] = trim($this->getCell($sheet, $row, $col));
						else{
							$cellData = strtolower(trim($this->getCell($sheet, $row, 'B'))); 
							if($cellData != 'id')
								break;
							else if($cellData == 'id'){
								$header[$key] = strtolower(trim($this->getCell($sheet, $row, $col)));
								if(strripos($header[$key], 'without tuning'))
									$header[$key] = 'build result';
								if(strripos(",".$header[$key], 'sourse'))
									$header[$key] = 'source name';
								continue;
							}
						}
						
					}	
				}
				if(!empty($header[$key])){
					$isDataStart = true;
// print_r("  ".$row."  ");
// print_r($header);
				}
			}
		}
	}
	
	protected function analyze_cover($sheet, $title){
		$this->info[$this->dir]['start_date'] =  $this->getCell($sheet, 6, 'G');
		$this->info[$this->dir]['end_date'] =   $this->getCell($sheet, 7, 'G');
		// get the build target
		$row = 19;
		$found = false;
		do{
			$scope = $this->getCell($sheet, $row, 'B');
			if ($scope == 'Scope'){
				$found = true;
				break;
			}
			$row ++;
		}while($row < 100);
		if ($found){
			$scope = $this->getCell($sheet, $row+2, 'B');
			$words = explode(',', $scope);
			$this->info[$this->dir]['build_target'] = preg_replace(array('/ target$/i', '/ /'), array('', '_'), trim(array_pop($words)));
		}
		else
			$this->info[$this->dir]['build_target'] = 'Release';
	}
	
	protected function process(){
		if(!empty($this->dir)){
			$this->info['cycle_id'] = $this->generateCycle();
			if($this->info['cycle_id'] != 'error'){
// print_r('cycle_id:'.$this->info['cycle_id']."  ");
				foreach($this->parse_result as $title=>$sheet_data){
					print_r("Processing sheet $title...\n<BR />");
					$this->processSheetData($title, $sheet_data);
					unset($this->parse_result[$title]);
				}
			}
		}
	}
	
	private function generateCycle(){
		$key = $this->dir;
		$cycle_type = isset($this->info[$key]['cycle_type']) ? $this->info[$key]['cycle_type'] : 'Fun';
		$myname  = isset($this->info[$key]['myname']) ? $this->info[$key]['myname'] : 'By_Excel';
		$compiler = isset($this->info[$key]['compiler']) ? $this->info[$key]['compiler'] : 'GCC';
		$build_target = isset($this->info[$key]['build_target']) ? $this->info[$key]['build_target'] : 'Release';
		$this->info['cycle']['upload_date'] = $this->excelTime($this->info['cycle']['upload_date']);
		
		$start_date = $end_date = $this->info['cycle']['upload_date'];
		if(!empty($this->info['cycle']['start_date']))
			$start_date = $this->excelTime($this->info['cycle']['start_date']);
		if(!empty($this->info['cycle']['end_date']))
			$end_date = $this->excelTime($this->info['cycle']['end_date']);
		$currentYear = (int)date('y', strtotime($start_date));
		$currentWorkWeek = (int)date('W', strtotime($start_date));
		$week = isset($this->info[$key]['week']) ? $this->info[$key]['week'] : sprintf("%2dWK%02d", $currentYear, $currentWorkWeek);
		//$rel = isset($this->info['cycle']['rel'] ? $this->info[$key]['rel'] : ;
	
		if(!empty($this->params['owner_id']))
			$this->info['cycle']['owner'] = $this->params['owner_id'];
		if (empty($info['cycle']['owner']))
			$this->info['cycle']['owner'] = 'Jian Zhang';
		$useradmin = dbFactory::get('useradmin');
		$res = $useradmin->query("SELECT id FROM users WHERE nickname=:nick", array('nick'=>$this->info['cycle']['owner']));
		$row = $res->fetch();
		$creater_id = $this->info['cycle']['owner_id'] = $row['id'];		

		if (preg_match('/^(.*)-(.*)$/i', $this->info[$key]['platform'], $matches)){
			$this->info[$key]['chip'] = $matches[1];
			$this->info[$key]['board_type'] = $matches[2];
			$board_type_id = $this->getElementId('board_type', array('name'=>$matches[1]));
			$chip_id = $this->getChipId('chip', array('name'=>$matches[2]));
			$prj =$matches[1]."-".$matches[2]."-".$this->info[$key]['os'];
			$os_id = $this->getElementId('os', array('name'=>$this->info[$key]['os']));
			if(!empty($board_type_id) && !empty($chip_id) )
				$prj_id = $this->getElementId('prj', array('name'=>$prj, 'chip_id'=>$chip_id, 'board_type_id'=>$board_type_id, 'os_id'=>$os_id), array('name'));
			$cycle = $prj."-".$cycle_type."-".$this->info['cycle']['rel']."-".$week."-".$myname;
			
			$cycle_type_id = $this->getElementId('cycle_type', array('name'=>$cycle_type));
			$testcase_type_id = $this->getElementId('testcase_type', array('name'=>'MQX'));
			$group_id = 6;//MQX
			$compiler_id = $this->getElementId('compiler', array('name'=>$compiler));
			$rel_id = $this->getElementId('rel', array('name'=>$this->info['cycle']['rel']));
			$build_target_id = $this->getElementId('build_target', array('name'=>$build_target));
		
			if(!empty($prj_id) && !empty($rel_id)){
				$cycleInfo = array('name'=>$cycle, 'start_date'=>$start_date, 'end_date'=>$end_date, 'build_target_id'=>$build_target_id, 
					'cycle_type_id'=>$cycle_type_id, 'compiler_id'=>$compiler_id, 'rel_id'=>$rel_id, 'prj_id'=>$prj_id, 'creater_id'=>$creater_id,
					'tester_ids'=>$creater_id, 'testcase_type_id'=>$testcase_type_id, 'group_id'=>$group_id, 'created'=>$start_date
				);
				$cycle_id = $this->getElementId('cycle', $cycleInfo, array('name'));
			}
			$this->info[$key]['prj_id'] = $prj_id;
			$this->info[$key]['rel_id'] = $rel_id;
			$this->info['cycle']['name'] = $cycle;
			return $cycle_id;
		}
		return 'error';
	}
	
	protected function processSheetData($title, $sheet_data){
// print_r('title:'.$title."  ");
		foreach($sheet_data as $row=>$case){
			if (empty($case['id']))
				continue;
			if(empty($case['testcase_module']))
				$case['testcase_module'] = $title;
			if(empty($case['testcase_testpoint']) || $this->params['config_file'] == APPLICATION_PATH.'/jqgrid/xt/testcase/android_kk.karen.config.php'){
				$case['testcase_testpoint'] = 'Default Testpoint For '.$case['testcase_module'];
			}
			if (empty($case['testcase_type'])){
				$case['testcase_type'] = 'MQX';
			}
			$caseinfo = $this->processCase($case);
			$cycle_detail_id = $this->processCycle($case, $caseinfo);
			$case['cycle_detail_id'] = $cycle_detail_id;
			$this->processLog($case);
		}	
	}	
	
	protected function processLog($case){
		if(!empty($case['actual result'])){
			$module = $case['testcase_module'];
			if (preg_match('/^(.*?)(_'.strtoupper($case['chip']).'_examples|_examples)$/', $module, $matches)){
				$module = $matches[1];
			}
			if(preg_match("/^(.*)\d{1}$/", $module, $matches))
				$module = $matches[1];
			$fileName = $case['source name'];
			// 如果存在'/'，则取/后面部分
			if (preg_match('/^(.*?)\\\.*\\\(.*)$/', $fileName, $matches)){			
				$fileName = $matches[2];
				$sub_dir = $matches[1];
			}
			if(empty($fileName))
				return;
			$log_dir = $this->root.'/'.$this->dir.'/'.$this->dir.'/'.strtoupper($module);
			//上传log的时候怎么办，也按照原来的方式上传吗？
			$extension = array('.txt', '.JPG', 'htm');
			$dest_dir = LOG_ROOT.'/'.$this->info['cycle']['name'].'_'.$this->info['cycle_id'].'/'.$case['id'].'_'.$case['cycle_detail_id'].'/'.$fileName.'.txt';
			foreach($extension as $ext){
				$dest = $logFile = '';
				$fdest = $dest_dir.'/'.$fileName.$ext;
				if (file_exists($log_dir.'/'.$fileName.$ext)){
					$logFile = $log_dir.'/'.$fileName.$ext;
					$dest = $fdest;
				}
				else{
					if(!empty($sub_dir)){
						if (file_exists($log_dir.'/'.$sub_dir.'/'.$fileName.$ext)){
							$logFile = $log_dir.'/'.$sub_dir.'/'.$fileName.$ext;
							$dest = $fdest;
						}
						else if(file_exists($log_dir.'/'.strtolower($module)."_".$sub_dir.'/'.$fileName.$ext)){
							$logFile = $log_dir.'/'.strtolower($module)."_".$sub_dir.'/'.$fileName.$ext;
							$dest = $fdest;
						}
							
					}
				}
				if(!empty($dest) && !file_exists($dest)){
					$dest = $this->tool->moveFile($logFile, $dest);
					// $dest = $this->tool->createDirectory($dest.'/'.$fileName, '.txt');
					if (!copy($logFile, $dest))
						print_r(">>>>>Failed to copy dest = $dest, fileName = $fileName"."\n<BR />");
				}
			}
		}
	}
	
	protected function processCase($case){
		if(empty($case['owner_id']))
			$case['owner_id'] = $this->info['cycle']['owner_id'];
		
		$case['updater_id'] = $case['owner_id'];
		$case['code'] = $case['id'];
		$case['summary'] = isset($case['source name'])? $case['source name']: 'Summary for '.$case['code'];
		$case['come_from'] = 'From Excel';
		if(!empty($case['priority'])){
			if(strlen($case['priority']) == 1)
				$testcase_priority_id = $case['priority'];
			else //p + num
				$case['testcase_priority'] = $case['priority'];
		}
		if(!empty($case['preconditions']))
			$case['precondition'] = $case['preconditions'];
		if(!empty($case['test steps']))
			$case['steps'] = $case['test steps'];
		if(!empty($case['expected result']))
			$case['expect_result'] = $case['expected result'];
		
		$transfer_fields = array('testcase_type', 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 
			'auto_level'=>'MANUAL', 'testcase_priority'=>'P3');
		$fields_value = $this->tool->extractItems($transfer_fields, $case);
		if (empty($fields_value['testcase_type']))
			$case['testcase_type_id'] = $this->params['testcase_type_id'];
			
		foreach($fields_value as $field=>$value){
			if (empty($case[$field.'_id'])){
				$case[$field.'_id'] = 0;
				if(!empty($value) || $value != ''){
					$valuePair = array('name'=>$value);
					if ($field == 'testcase_testpoint'){
						$valuePair['testcase_module_id'] = $case['testcase_module_id'];
					}
					else if ($field == 'testcase_module'){
						$valuePair['testcase_type_ids'] = $case['testcase_type_id'];
					}
					$case[$field.'_id'] = $this->getElementId($field, $valuePair, array('name'));
					if($field == 'testcase_module'){
						$this->tool->getElementId("testcase_module_testcase_type", array('testcase_module_id'=>$case[$field.'_id'], 'testcase_type_id'=>$case['testcase_type_id']));
					}
				}
				unset($case[$field]);
			}
		}
		
		$case_fields = array('testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 'code', 'summary'=>'', 'testcase_category_id', 'testcase_source_id', 'come_from');
		$case_value = $this->tool->extractItems($case_fields, $case);
		$newCase = false;
		$case_id = $this->getElementId('testcase', $case_value, array('code'), $newCase);
		if (!$newCase){
			print_r($case_value['code']." already existed \n<BR />");
		}
		if (!empty($case['tag']))
			$this->tag['element_id'][] = $case_id;
		$updated = $this->excelTime($this->info['cycle']['upload_date']);;
		$ver_fields = array('auto_level_id'=>AUTO_LEVEL_MANUAL, 'testcase_priority_id'=>3, 'auto_run_minutes'=>0, 'manual_run_minutes'=>0, 'command'=>' ', 
			'objective'=>'NA', 'precondition'=>'NA', 'steps'=>'NA', 'expected_result'=>'NA', 'resource_link'=>'NA', 'parse_rule_id'=>1, 'parse_rule_content'=>' ', 
			'owner_id', 'updater_id', 'updated'=>$updated);
		$ver_values = $this->tool->extractItems($ver_fields, $case);
		$ver_values['testcase_id'] = $case_id;
		$ver_values['edit_status_id'] = EDIT_STATUS_PUBLISHED;
		if(!empty($testcase_priority_id))
			$ver_values['testcase_priority_id'] = $testcase_priority_id;
		$newVer = false;
		$version_id = $this->getElementId('testcase_ver', $ver_values, array(), $newVer);
// print_r("newVer:".$newVer."  "."verid:".$version_id."  ");
		if ($newVer && !$newCase){
			$res = $this->tool->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->tool->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>$updated), "id=$version_id");
		}
		$ver = array('testcase_id'=>$case_id, 'testcase_ver_id'=>$version_id);
		// if ($newVer){
print_r("<<<<<<<<<<<<<<<<<<<<<<<<<<<<new Ver >>>>>>>>>>>>>>>>>>> \n<BR />");
			$link = $ver;
			$key = $this->dir;
			$prj_id = $this->info[$key]['prj_id'];
			$rel_id = $this->info[$key]['rel_id'];
			$link['prj_id'] = $prj_id;
			$history = array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'act'=>'remove');
			$res0 = $this->tool->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id".
				" AND testcase_id={$ver['testcase_id']} AND testcase_ver_id={$ver['testcase_ver_id']}".
				" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
			if($row0 = $res0->fetch()){
				continue;
			}
			else{
				$res1 = $this->tool->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id".
				" AND testcase_id={$ver['testcase_id']}".
				" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
				while($row = $res1->fetch()){
					$this->tool->delete('prj_testcase_ver', "prj_id=$prj_id AND testcase_id={$ver['testcase_id']}");
					$history['testcase_ver_id'] = $row['testcase_ver_id'];
					$history['act'] = 'delete';
					$this->tool->insert('prj_testcase_ver_history', $history);
				}
				$history['testcase_ver_id'] = $ver['testcase_ver_id'];
				$history['act'] = 'add';
				$this->tool->insert('prj_testcase_ver', $link);
				$this->tool->insert('prj_testcase_ver_history', $history);
			}
		// }
// print_r($ver);
		return $ver;
	}
	
	protected function processCycle($case, $caseInfo){
		$key = $this->dir;
		$prj_id = $this->info[$key]['prj_id'];
		$rel_id = $this->info[$key]['rel_id'];
		
		$cycleInfo = array();
		$cycleInfo['build_result_id'] = 0;
		if($case['build result'])
			$cycleInfo['build_result_id'] = $this->getResultId($case['build result']);
		$cycleInfo['result_type_id'] = 0;
		if($case['result']){
			$cycleInfo['result_type_id'] = $this->getResultId($case['result']);
			if($cycleInfo['result_type_id'] == 'ongoing')
				$cycleInfo['result_type_id'] = 0;
			if (strtoupper($case['result']) == 'FAIL'){ // check if there're FSL tracking number
				$track_num = $this->getTrackNumber($this->info[$key]['board_type'], $this->info[$key]['compiler'], $case['id'], $case['testcase_module']);
//print_r("source_name = $source_name, board = $board, comp = $compiler, tracknum = $track_num\n");
				if (!empty($track_num))
					$case['freescale tracking number'] = $track_num;
			}
		}
		$last_run = $this->info['cycle']['upload_date'];
// print_r('last_run:'.$last_run."  "."date:".$this->info['cycle']['upload_date']."  ");
		$cycleInfo['issue_comment'] = isset($case['status of bug'])?$case['status of bug']:'';
		$cycleInfo['defect_ids'] = isset($case['cr id']) ? $case['cr id'] : (isset($case['freescale tracking number']) ? $case['freescale tracking number'] : '');
		$cycle_detail = array('cycle_id'=>$this->info['cycle_id'], 'testcase_id'=>$caseInfo['testcase_id'], 'testcase_ver_id'=>$caseInfo['testcase_ver_id'], 
			'build_result_id'=>$cycleInfo['result_type_id'], 'result_type_id'=>$cycleInfo['result_type_id'] ,'defect_ids'=>$cycleInfo['defect_ids'], 
			'issue_comment'=>$cycleInfo['issue_comment'], 'finish_time'=>$last_run, 'codec_stream_id'=>0, 'test_env_id'=>1
		);
		$cycle_detail_id = $this->getElementId('cycle_detail', $cycle_detail);
		
		// 更新testcase_last_result
		$res = $this->tool->query("select * from testcase_last_result WHERE testcase_id={$caseInfo['testcase_id']} and rel_id={$rel_id} and prj_id={$prj_id}");
		if ($row = $res->fetch()){
			if ($row['tested'] < $last_run){
				$data = array('cycle_detail_id'=>$cycle_detail_id, 'result_type_id'=>$cycle_detail['result_type_id'], 'tested'=>$last_run);
				$this->tool->update('testcase_last_result', $data, "id=".$row['id']);
			}
		}
		else{
			$data = array('testcase_id'=>$caseInfo['testcase_id'], 'rel_id'=>$rel_id, 'prj_id'=>$prj_id, 'cycle_detail_id'=>$cycle_detail_id, 'result_type_id'=>$cycle_detail['result_type_id'], 'tested'=>$last_run);
			$this->tool->insert('testcase_last_result', $data);
		}
		// 更新testcase.last_run
		$res = $this->tool->query("select * from testcase where id={$caseInfo['testcase_id']}");
		$row = $res->fetch();
//print_r("finish_time = $finish_time, last_run={$row['last_run']}, case_id={$caseInfo['testcase_id']}\n");
		if ($row['last_run'] < $last_run){
			$this->tool->update('testcase', array('last_run'=>$last_run), 'id='.$caseInfo['testcase_id']);
		}
		return $cycle_detail_id;
	}
	
	protected function getResultId($result){
		$result = strtolower($result);
		switch(strtolower($result)){
			case 'ok':
			case 'pass':
				$result = 'Pass';
				break;
			case 'fail':
			case 'nok':
				$result = 'Fail';
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
			case 'on going':
				$result = 'Ongoing';
				break;
			case 'timeout':
			case 'time out':
				$result = 'Time Out';
				break;
		}
		if (stripos($result, 'not support') !== false)
			$result = 'N/S';
		if($result == '')
			return 0;
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
		if(!stripos($date, "-")){
			if(preg_match('/^(\d{4})(\d{2})(\d{2})$/', $date, $matches)){
				$date = $matches[1]."-".$matches[2]."-".$matches[3];
			}
		}
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
	
	private function readExcel($fileName){
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($fileName);
		/**  Create a new Reader of the type that has been identified  **/
		$reader = PHPExcel_IOFactory::createReader($inputFileType);
		$reader->setReadDataOnly(true);
		$objExcel = $reader->load($fileName);
		return $objExcel;
	}

	private function analyze_defect_list($dir){
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
	

	private function getTrackNumber($board, $compiler, $source_name, $module_name = ''){
		$track_num = '';
		$board = strtolower($board);
		$compiler = strtolower($compiler);
		$source_name = strtolower($source_name);		
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