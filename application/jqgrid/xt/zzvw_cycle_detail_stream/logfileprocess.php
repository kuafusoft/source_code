<?php
require_once(str_replace("\\", "/", dirname(__FILE__)).'/logfileparser.php');

class cycle_playlist{
    private $db;
	public function __construct($dbName){
		$resource = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getResource('multidb');
		try{
			$this->db = $resource->getDb($dbName);
		}catch(Exception $e){
			// $this->db = $this->tool->getDb($dbName);
		}
		$this->db->setFetchMode(Zend_Db::FETCH_ASSOC);
	}

	function uploadLog($logFile, $cycle_id){
		$this->handleLogFile($cycle_id, $logFile);
	}        

	function uploadSingleLog($logfile, $elementId){
		// rename the file
		// get the suffix name of logfile
		$suffix = '';
		if (preg_match("/.*(\d+\..{1,5})$/", basename($logfile), $matches)){
			$suffix = $matches[1];
		}
		else if (preg_match("/(\..{1,5})$/", basename($logfile), $matches)){
			$suffix = $matches[1];
		}
		$sql = 'SELECT cycle.prj_id, prj.name as prj, tc.code as code 
			FROM cycle, cycle_detail detail, testcase tc, prj
			WHERE cycle.prj_id=prj.id AND detail.cycle_id=cycle.id AND detail.testcase_id=tc.id
			AND detail.id='.$elementId;
	//echo ('logfile='.basename($logfile).' recordid='.$recordid.' suffix='.$suffix.' sql='.$sql);    
		$res = $this->db->query($sql);
		if ($row = $res->fetch()){
			//改掉GetFilePathSeparat
			$newName = dirname($logfile).GetFilePathSeparate().$row['code'].'_['.$row['prj'].']'.$suffix;
			if (!file_exists($newName))
				rename($logfile, $newName); 
		}            
	}

	function handleLogFile($cycle_id, $fileName){
		//是处理上传已有cycle_id的情况？？？？？
		$res = $this->db->query("SELECT *, prj.name as name, prj.os_id as os_id FROM cycle LEFT JOIN prj ON cycle.prj=prj.id WHERE id=".$cycle_id);
		$cycleInfo = $res->fetch();
		$relFileName = basename($fileName);
		if (stripos(strtolower($relFileName), '.zip') !== false){
			// analyze the file
			$cmd = 'unzip -v '.$fileName;
			$line = exec($cmd, $output, $retVal);
			if ($retVal){ // failed
				print_r("retVal = $retVal");
				return;
			}
			$multiFiles = count($output) > 6;
			if ($multiFiles){
				$this->handleMultiFiles($cycleInfo, $fileName);
				return;
			}
			$cmd = "gunzip -S .zip '".$fileName."'";
			system($cmd, $retValue);
			$fileName = dirname($fileName).'/'.basename($fileName, '.zip');
		}
		
		$handled = false;
	/*
		if (stripos(strtolower($relFileName), '.xls') !== false){
			$handled = handleXlsFile($cycleInfo, $fileName);
			return $handled;
		}
	*/    
		$fileHandle = fopen($fileName, "r");
		if ($fileHandle){
			if (stripos(strtolower($relFileName), '.xls') !== false && ($cycleInfo['os_id'] == 1 || $cycleInfo['os_id'] == 4)){
				$handled = $this->handleXlsFile($cycleInfo, $fileHandle);
			}
			else if (stripos(strtolower($relFileName), '.arobot') !== false){
				$handled = $this->handleaRobotFile($cycleInfo, $fileHandle);
			}
			else{
				if ($cycleInfo['os_id'] == 2 || $cycleInfo['os_id'] == 3 || $cycleInfo['os_id'] == 5) // WinCE
					$handled = $this->handleCETKLogFile($cycleInfo, $fileHandle);
				if (!$handled){
					if ($cycleInfo['os_id'] == 1 || $cycleInfo['os_id'] == 4){ // linux
						// try if it's a android CTS result logfile
						if ($cycleInfo['os_id'] == 4 && stripos(strtolower($relFileName), '.xml') !== false)
							$handled = $this->handleAndroidCTSResultLogFile($cycleInfo, $fileHandle);
						else{// try if it's a linux bsp logfile
							$handled = $this->handleLinuxBSPResultLogFile($cycleInfo, $fileHandle);
						}
						if (!$handled){
							$handled = $this->handleLinuxBSPOutputLogFile($cycleInfo, $fileHandle);
						}
					}
				}
				
				if (!$handled){ // it's a Codec log file?
					$handled = $this->handleCodecLogFile($cycleInfo, $fileHandle);
				}
			}
			fclose($fileHandle);
		}
		return $handled;
	}

	function handleAndroidCTSResultLogFile($cycleInfo, $handle){
		$handled = false;
		$parser = new CTSLogFileParser($handle, array('commandLine_prefix'=>'./startcts', 'commandLine_plan'=>'FSLTests', 'db'=>$this->db, 'cycleInfo'=>$cycleInfo));
		$result = $parser->parse();
		if ($result['result']['code']){
			print_r($result['result']['msg']);
		}
		else
			$handled = true;
		return $handled;
	}

	function handleaRobotFile($cycleInfo, $handle){
		$handled = false;
		$parser = new aRobotLogFileParser($handle, array('db'=>$this->db, 'cycleInfo'=>$cycleInfo));
		$result = $parser->parse();
		if ($result['result']['code']){
			print_r($result['result']['msg']);
		}
		else
			$handled = true;
		return $handled;
	}

	function handleXlsFile($cycleInfo, $handle){
		$handled = false;
		$parser = new CXlsLogFileParser($handle, array('db'=>$this->db, 'cycleInfo'=>$cycleInfo));
		$result = $parser->parse();
		if ($result['result']['code']){
			print_r($result['result']['msg']);
		}
		else
			$handled = true;
		return $handled;

		rewind($handle);
		if (!feof($handle)){
			$line =fgets($handle);
			if (count($line) == 0)
				return $handled;
			if (!preg_match("/^BitStreams\sFinal Result\s.*\tRemark/", $line, $matches)){ // Not Codec Linux Logfile
				print_r("NOT Linux Codec Log File\n");            
				return $handled;
			}
		}
		$handled = true;
		while(!feof($handle)){
			$line =fgets($handle);
			if (count($line) == 0)
				continue;
	print_r($line);            
			if (preg_match("/^.*?([^\/]*?)\t(.*?)\t.*\t(.*?)$|^.*?([^\/]*?)\t(.*?)$/", $line, $matches)){
	print_r($matches);        
				$caseInfo = array();
				if (count($matches) == 4){
					$caseInfo['title'] = trim($matches[1]);
					$caseInfo['result'] = trim($matches[2]);
					$caseInfo['comment'] = trim($matches[3]);
				}else if (count($matches) == 6){
					$caseInfo['title'] = trim($matches[4]);
					$caseInfo['result'] = trim($matches[5]);
					$caseInfo['comment'] = '';
				}
	/*
				if (preg_match("/^.*\/(.*?)$/", trim($caseInfo['title']), $matches)){
					$caseInfo['title'] = $matches[1];
				}
	*/
	print_r($caseInfo);
				if (trim(strtoupper($caseInfo['result'])) == 'PASS'){
					$result_type_id = 1;    // pass
	/*
					// get the current result first
					$sql = "SELECT result_type_id FROM cycle_detail tr LEFT JOIN testcase tc ON tr.testcase_id=tc.id ".
						" WHERE tr.cycle_id=".$cycleInfo['id']." AND trim(tc.code)=".$db->AddSingleQuotes($caseInfo['title']);//?????????????????
					$result = $db->Query($sql);
					if ($row = $result->fetchRow()){
						if (!empty($row['result_type_id']) && $row['result_type_id'] != 1)
							$result_type_id = $row['result_type_id'];
					}
	//print_r($sql);
	*/
					$sql = "UPDATE cycle_detail detail LEFT JOIN testcase tc ON detail.testcase_id=tc.id ".
						" SET detail.finish_time=NOW(), detail.result_type_id=$result_type_id, ".
						"detail.comment=CONCAT_WS('', detail.comment, ".$db->AddSingleQuotes($caseInfo['comment']).")".
						" WHERE detail.cycle_id=".$cycleInfo['id']." AND trim(tc.code)=".$db->AddSingleQuotes($caseInfo['title']).
						" AND (detail.result_type_id=0 OR ISNULL(detail.result_type_id))";//???????????????????
					$db->Execute($sql);
				}
			}
		}

	/*    
		$data = new Spreadsheet_Excel_Reader();
		$data->setOutputEncoding('CP1251');
		$data->read($fileName);
		// check if it's the Codec Linux Logfile, only check the first line
		$columns = $data->sheets[0]['numCols'];
		$rows = $data->sheets[0]['numRows'];
		if ('BITSTREAMS' == strtoupper(trim($data->sheets[0]['cells'][1][1])) && 
			'FINAL RESULT' == strtoupper(trim($data->sheets[0]['cells'][1][2])) &&
			'REMARK' == strtoupper(trim($data->sheets[0]['cells'][1][$columns]))){
			for ($i = 2; $i <= $rows; $i++) {
				$caseInfo = array();
				$caseInfo['title'] = trim($data->sheets[0]['cells'][$i][1]);
				if (preg_match("/^.*\/(.*?)$/", trim($data->sheets[0]['cells'][$i][1]), $matches)){
					$caseInfo['title'] = $matches[1];
				}
				$caseInfo['result'] = $data->sheets[0]['cells'][$i][2];
				$caseInfo['comment'] = isset($data->sheets[0]['cells'][$i][$columns]) ? $data->sheets[0]['cells'][$i][$columns] : '';
				if (strtoupper(trim($caseInfo['result'])) == 'PASS'){
	//print_r($caseInfo);
					$sql = "UPDATE cycle_detail detail LEFT JOIN testcase tc ON detail.testcase_id=tc.id SET detail.result_type_id=1, ".
						"detail.comment=CONCAT(detail.comment, ".$db->AddSingleQuotes($caseInfo['comment']).")".
						" WHERE detail.cycle_id=".$cycleInfo['id']." AND trim(tc.code)=".$db->AddSingleQuotes($caseInfo['title']);//???????????????????
					$db->Execute($sql);
				}
			}
		}
	*/    
	}

	function calcDirName($cycleInfo, $caseName, &$detail_id = 0, &$result_type_id = 0, $isCodec = false){
		$sql = "select detail.id as detail_id, detail.result_type_id from cycle_detail detail, testcase tc 
			where tc.code='".$caseName."' AND detail.testcase_id=tc.id AND detail.cycle_id=".$cycleInfo['id'];
		if (!empty($isCodec)){
			$sql = "select tc.testcase_id, detail.id as detail_id, detail.result_type_id from cycle_detail detail, testcase tc 
				where tc.code=".$this->db->AddSingleQuotes($caseName)." AND detail.testcase_id=tc.id AND detail.cycle_id=".$cycleInfo['id'];
		}
		$res = $this->db->query($sql);
		$detail_id = 0;
		if ($row = $res->fetch()){
			$detail_id = $row['detail_id'];
			$result_type_id = $row['result_type_id'];
			if ($isCodec)
				$caseName = $row['testcase_id'];
		}
		else{
	print_r("sql = $sql, caseName = $caseName\n");    
		}
		$caseLogFileName = GetLogRoot().$cycleInfo['name'].'_'.$cycleInfo['id'].GetFilePathSeparate().$caseName.'_'.$detail_id.GetFilePathSeparate();
	//print_r("logfilename =$caseLogFileName\n");
		CreateDirectory($caseLogFileName);
		$caseLogFileName .= $caseName.'_['.$cycleInfo['prj'].']';//??????????????????
	/*
	if ($detail_id==0){
		print_r("sql = $sql, caseName = $caseName, result = $result_type_id");
	} 
	*/   
		return $caseLogFileName;
	}

	function updateTestResult($detail_id, $result){
		$this->db->update('cycle_detail', array('result_type_id'=>$result), 'id='.$detail_id);
	}

	function newFile($caseInfo){
		$fileName = $caseInfo['logfile'].'.log';
		if (file_exists($fileName))
			$fileName = $this->availableName($fileName).'.log';
		$handle = fopen($fileName, 'w');
		return $handle;
	}

	function closeFile($caseInfo, $type = ''){
		fclose($caseInfo['handle']);
		if (empty($type)){
			if (!empty($caseInfo['fail']))
				$type .= '_Failed'.$caseInfo['fail'];
			if (!empty($caseInfo['skip']))
				$type .= '_Skipped'.$caseInfo['skip'];
			if (!empty($caseInfo['abort']))
				$type .= '_Aborted'.$caseInfo['abort'];
			if (empty($type)){
				if (!empty($caseInfo['iscodec']))
					$type = '_'.$caseInfo['result'];
				else
					$type = '_Passed';
			}
		}
		$rename = $caseInfo['logfile'].$type.'.log';
		if (file_exists($rename))
			$rename = $this->availableName($rename).'.log';
		rename($caseInfo['logfile'].'.log', $rename); 
		if (FILESYSTEM=="UNIX")
			system('chmod 777 '. $rename);
	}

	function endPreCase($caseInfo, $caseFinished){
	print_r($caseInfo);
		if ($caseFinished)
			$type = '';
		else if (!empty($caseInfo['iscetk']) || !empty($caseInfo['iscodec']))
			$type = '_HUNG';
		else
			$type = '_UNKNOWN';
		$this->closeFile($caseInfo, $type);
		if (!empty($caseInfo['detail_id']) && $caseFinished){
			$result = $caseInfo['result'];
			if (!empty($caseInfo['iscodec'])){
				if (trim($result) != 'Pass')
					return;
				$result = 1;// pass
			}
			if (!empty($caseInfo['result_type_id']) && $caseInfo['result_type_id'] != 1)// not pass
				$result = $caseInfo['result_type_id'];
			$sql = 'UPDATE cycle_detail SET result_type_id='.$result.',finish_time=NOW()';//.date('Y-m-d H:i:s').'"';
			if (!empty($caseInfo['subinfo'])){
				$sql .= ", comment=CONCAT_WS('', comment, '".$this->db->escape($caseInfo['subinfo'])."')";
			}                
			$sql .= ' WHERE id='.$caseInfo['detail_id'];
			$this->db->query($sql);
		}
	}

	function handleDir($cycleInfo, $dirName){
	//print_r("dirName = $dirName\n");
		$files = scandir($dirName);
		if ($files){
			foreach($files as $key=>$file){
				if ($file == '.' || $file == '..')
					continue;
				$file = $dirName.GetFilePathSeparate().$file;
				if (is_dir($file))
					handleDir($cycleInfo, $file);
				else
					handleFile($cycleInfo, $file);
			}
		}
		rmdir($dirName);
	}

	function handleFile($cycleInfo, $fileName){
	//print_r("fileName = $fileName\n");
		if (preg_match("/^\[(.*)\].*?_([^_]*)?\.log$/", $fileName, $matches)){
			$caseID = $matches[1];
			$caseLogFileName = $this->calcDirName($cycleInfo, $caseID, $detail_id, $result_type_id).'.log';
			if (!empty($matches[2])){
				if (strtoupper($matches[2]) == 'PASSED' && $result_type_id <= 1)
					$result_type_id = 1;
				else
					$result_type_id = 2;
			}
			if (file_exists($caseLogFileName))
				$caseLogFileName = $this->availableName($caseLogFileName);
			rename($tmpDir.$file, $caseLogFileName);
			if ($result_type_id && $detail_id){
				$this->db->update('cycle_detail', array('result_type_id'=>$result_type_id), 'id='.$detail_id);
			}
		}
		else{
			$fileHandle = fopen($fileName, "r");
			if ($fileHandle)
				$handled = $this->handleCETKLogFile($cycleInfo, $fileHandle);
		}
		unlink($fileName);
	}

	function handleMultiFiles($cycleInfo, $fileName){
		$tmpDir = GetLogRoot().$cycleInfo['name'].'_'.$cycleInfo['id'].GetFilePathSeparate().'tmp'.GetFilePathSeparate();
		CreateDirectory($tmpDir);
		$cmd = 'unzip -d '.$tmpDir.' '.$fileName;
		exec($cmd);
		$files = scandir($tmpDir);
	//print_r($files);    
		if ($files){
			foreach($files as $key=>$file){
				if ($file == '.' || $file == '..')
					continue;
				$file = $tmpDir.$file;
				if (is_dir($file)){
					$this->handleDir($cycleInfo, $file);
				}
				else{
					$this->handleFile($cycleInfo, $file);
	/*                
					if (preg_match("/^\[(.*)\].*?_([^_]*)?\.log$/", $file, $matches)){
                    $caseID = $matches[1];
						$caseLogFileName = calcDirName($cycleInfo, $caseID, $detail_id, $result_type_id).'.log';
						if (!empty($matches[2])){
							if (strtoupper($matches[2]) == 'PASSED' && $result_type_id <= 1)
								$result_type_id = 1;
							else
								$result_type_id = 2;
						}
						if (file_exists($caseLogFileName))
							$caseLogFileName = availableName($caseLogFileName);
						rename($tmpDir.$file, $caseLogFileName);
						if ($result_type_id && $detail_id){
							$db->UpdateRecord('cycle_detail', array('result_type_id'=>$result_type_id), 'id='.$detail_id);
						}
					}
					else{
						$fileHandle = fopen($fileName, "r");
						if (!$fileHandle){
							unlink($tmpDir.$file);
							continue;
						}
						$handled = handleCETKLogFile($cycleInfo, $fileHandle);
						unlink($tmpDir.$file);
					}
	*/                
				}
			}
			rmdir($tmpDir);
		}
		return true;
	}

	function handleLinuxBSPResultLogFile($cycleInfo, $handle){
		rewind ($handle);
		return false;
	}

	function handleLinuxBSPOutputLogFile($cycleInfo, $handle){
		rewind ($handle);
		return false;
	}

	function handleCodecLogFile($cycleInfo, $handle){
		$handled = false;
		$parser = new CCodecLogFileParser($handle, array('db'=>$this->db, 'cycleInfo'=>$cycleInfo));
		$result = $parser->parse();
		if ($result['result']['code']){
			print_r($result['result']['msg']);
		}
		else
			$handled = true;
		return $handled;

		$handled = false;
		$caseInfo = array();
		$caseStart = false;
		$caseFinished = false;
		rewind ($handle);
		while(!feof($handle)){
			$line = fgets($handle);
			if (count($line) == 0)
				continue;
			if (preg_match('/^.*.exe ".*\\\\(.*?)"/', $line, $matches)){
				if ($caseStart){
					$this->endPreCase($caseInfo, $caseFinished);
					unset($caseInfo);
				}
				$handled = true;
				$caseStart = true;
				$caseFinished = false;
				$caseInfo['iscodec'] = true;
				$caseInfo['title'] = $caseInfo['code'] = trim($matches[1]);
	/*
				$sql = 'SELECT testcase_id FROM testcase WHERE trim(code)="'.$caseInfo['title'].'"';//?????????????
				$db = new Dbop();
				$result = $db->Query($sql);
				if ($row = $result->fetchRow())
					$caseInfo['code'] = $row['code'];
				else{
	print_r("title={$caseInfo['title']}, sql = $sql\n");       
				}         
	*/            
				$caseInfo['logfile'] = $this->calcDirName($cycleInfo, $caseInfo['title'], $detail_id, $result_type_id, true);//??????????????
				$caseInfo['handle'] = newFile($caseInfo);
				if (!$caseInfo['handle'])
					break;
				$caseInfo['detail_id'] = $detail_id;
				$caseInfo['result_type_id'] = $result_type_id;
				fwrite($caseInfo['handle'], $line);
				continue;
			}
			if(!$caseStart){
				continue;            
			}
			fwrite($caseInfo['handle'], $line);
			if (preg_match("/^\s*Test Result : (\S*)\s$/", $line, $matches)){
				$caseInfo['result'] = $matches[1];
				$caseFinished = true;
			}
		}
		if ($caseStart){
			$this->endPreCase($caseInfo, $caseFinished);
		}
		return $handled;
	}

	function handleCETKLogFile($cycleInfo, $handle){
		$handled = false;
		$parser = new CCETK7LogFileParser($handle, array('db'=>$this->db, 'cycleInfo'=>$cycleInfo));
		$result = $parser->parse();
		if ($result['result']['code']){
			print_r($result['result']['msg']);
		}
		else
			$handled = true;
		return $handled;

		$caseInfo = array();
		
		$result_type_id = 0;
		$subInfo = '';
		$isSubCase = false;
		$caseStart = false;
		$caseFinished = false;
		rewind ($handle);
		$pattern = "/^.. call .*\[(.*)\].*?(-.*?)?\.bat.*$/sm";
		while (!feof($handle)){
			$line = fgets($handle);
			if (count($line) == 0)
				continue;
			if (preg_match($pattern, $line, $matches)){
				if ($caseStart)
					$this->endPreCase($caseInfo, $caseFinished);

				$handled = true;
				$caseStart = true;
				$caseFinished = false;
				$caseInfo['code'] = $matches[1];
				$caseInfo['keepsection'] = '';
				if (!empty($matches[2]))
					$caseInfo['keepsection'] = $matches[2];
				$caseInfo['logfile'] = $this->calcDirName($cycleInfo, $caseInfo['code'], $detail_id, $result_type_id).$caseInfo['keepsection'];
				$caseInfo['handle'] = newFile($caseInfo);
				if (!$caseInfo['handle'])
					break;
				fwrite($caseInfo['handle'], $line);
				$caseInfo['detail_id'] = $detail_id;
				$caseInfo['result_type_id'] = $result_type_id;
				$caseInfo['result'] = 1; // preset to pass
				$caseInfo['fail'] = $caseInfo['skip'] = $caseInfo['abort'] = 0;
				$caseInfo['subinfo'] = '';
				$caseInfo['iscetk'] = false;
				$isSubCase = false;
				continue;
			}
			if (!$caseStart)
				continue;

			if(preg_match("/<TESTGROUP>/", $line)){
				$caseInfo['iscetk'] = true;
	/*
				if ($caseFinished){
					closeFile($caseInfo);
					$caseInfo['handle'] = newFile($caseInfo);
					if (!$caseInfo['handle'])
						break;
					$handled = true;
				}
	*/            
			}
			fwrite($caseInfo['handle'], $line);
			if (!$caseInfo['iscetk'])
				continue;

	//print_r($caseInfo);
			// Get the sub-case information, including id, name, result. The sub-case information will be in the comment
			if (preg_match("/^\s*\W\W\W TEST COMPLETED/", $line, $matches)){
	//print_r("issubCase\n");
				$isSubCase = true;
				$oneSubInfo = array();
				continue;
			}
			if ($isSubCase){
				if (preg_match("/^\s*\W{3} Test Name: *(.*)$/", $line, $matches))// get test name
					$oneSubInfo['name'] = $matches[1];
				else if (preg_match("/^\s*\W{3} Test ID: *(.*)$/", $line, $matches)) // get test id
					$oneSubInfo['id'] = $matches[1];
				else if (preg_match("/^\s*\W{3} Result: *(.*)$/", $line, $matches)){    // get result
					$oneSubInfo['result'] = $matches[1];
					if (stripos($oneSubInfo['result'], 'pass') === false){
						if (!empty($oneSubInfo['name']))
							$caseInfo['subinfo'] .= ' Test Name:'.$oneSubInfo['name'];
						if (!empty($oneSubInfo['id']))
							$caseInfo['subinfo'] .= ' id:'.$oneSubInfo['id'];
						if (!empty($oneSubInfo['result']))
							$caseInfo['subinfo'] .= ' Result:'.$oneSubInfo['result'];
						$caseInfo['subinfo'] .= "\r\n";
					}
					$isSubCase = false;
					$oneSubInfo = null;
				}
			}
			// Get the final result information
			if(preg_match("/^\s*\W\W\W Failed: *([0-9]*)/", $line, $matches)){
				$caseInfo['fail'] = (int)$matches[1];
				if ($caseInfo['fail']) $caseInfo['result'] = 2;
			}
			else if(preg_match("/^\s*\W\W\W Skipped: *([0-9]*)/", $line, $matches)){
				$caseInfo['skip'] = (int)$matches[1];
				if ($caseInfo['skip']) $caseInfo['result'] = 2;
			}
			else if(preg_match("/^\s*\W\W\W Aborted: *([0-9]*)/", $line, $matches)){
				$caseInfo['abort'] = (int)$matches[1];
				if ($caseInfo['abort']) $caseInfo['result'] = 2;
			}
			else if (preg_match("/^<\/TESTGROUP>/", $line)){
				$caseFinished = true;
			}
		}
		if ($caseStart){
			$this->endPreCase($caseInfo, $caseFinished);
		}
		return $handled;
	}    
	/*
	function availableName($fileName){
		$base = dirname($fileName).'/'.basename($fileName,'.log');
		$i = 1;
		$ret = $base;    
		do{
			$ret = $base.'_'.$i++;
		}while(file_exists($ret.'.log'));
		return $ret;
	}
	*/
	function GenTestCycleCmdFile($intTestcycle_id,$intIsAuto=1)
	{
		if (!($intTestcycle_id > 0) ) return false;
		$sql = "select testcase.id as testcase_id, testcase_ver.command as cmd 
			from testcase, testcase_ver, cycle_detail 
			where cycle_detail.cycle_id =$intTestcycle_id and cycle_detail.testcase_ver_id =testcase_ver.id 
			and testcase.id = testcase_ver.testcase_id and testcase_ver.isauto=".($intIsAuto == 1 ? 1 : 3);
		
		$res = $this->db->query($sql);
		$str = '';
		while ($row = $res->fetch())){
			$str .= $row["testcase_id"] . " " . $row["cmd"] . "\n";
		}
		return $str;
	}

	function GenCmdFileByCase($platform,$os,$caselist){
		$sql = "select ptv.testcase_ver_id,testcase.id as testcase_id,testcase_ver.command as cmd".
				" from prj_testcase_ver ptv, testcase, testcase_ver where testcase.id in (". trim($caselist) .
				") and testcase.id=ptv.testcase_id and ptv.prj_id=".$platform . 
				" and ptv.os_id=".$os;//?????????????????/
		$res = $this->db->query($sql);
		$str = '';
		while ($row = $res->fetch()){
				$str .=$row["testcase_id"] . " " . $row["cmd"] . "\n";
		}
		return $str;
	}

	function GenCmdFileBydetail_id($testcase_ver_id, $os){
		$prePath = "FAKEPATH";

		$sql = "select testcase.testcase_type_id as typeid".
				" from testcase, cycle_detail where cycle_detail.id in (". trim($testcase_ver_id)  . 
				") and cycle_detail.testcase_id=testcase.id";
		$res = $this->db->query($sql);
		while ($row = $res->fetch()){
			if ($row['typeid'] == 1){
				$sql = "select testcase.testcase_type_id as typeid, testcase.id as testcase_id,testcase_ver.command as cmd".
					" from testcase_ver,testcase,cycle_detail where cycle_detail.id in (". trim($testcase_ver_id)  . 
					") and testcase.id=testcase_ver.testcase_id and cycle_detail.testcase_ver_id=testcase_ver.id";
			}
			elseif ($row['typeid'] == 2){
				$sql = "select testcase.testcase_module_id as testcase_module_id, testcase.testcase_type_id as typeid, testcase.code as casename, testcase_ver.resource_link as resource_link, testcase.id as testcase_id,testcase_ver.command as cmd".
					" from testcase_ver,testcase,cycle_detail where cycle_detail.id in (". trim($testcase_ver_id)  . 
					") and testcase.id=testcase_ver.testcase_id and cycle_detail.testcase_ver_id=testcase_ver.id order by testcase.testcase_module_id";
			}
		}
		
		$res = $this->db->query($sql);
		$cnt = 0;
		$needSpecEnd = 0;
	$lastModuleId = -1;
		$str = '';
		while ($row = $res->fetch()){
			if ($row['typeid'] == 1){
				$str .= $row["testcase_id"] . " " . $row["cmd"] . "\n";
			}
			elseif ($row['typeid'] == 2){
				if ($row['testcase_module_id'] != $lastModuleId && $lastModuleId != -1){
					if ($os == 2 || $os == 3){	
						$str .= "</ASX>\n";
						$cnt = 0;
					}
					$str .= "\n";
				}
				$lastModuleId = $row['testcase_module_id']; 
		
				if ($os == 1){
					$str .= "{$row['casename']}\n";
					$changeResLink = str_replace("\\", "/", $row['resource_link']);
					$str .= "$prePath$changeResLink\n";
				}
				else if ($os == 2 || $os == 3){
					if ($cnt == 0){
						$str .= "<ASX version = \"3.0\">\n";
						$str .= "    <PARAM name = \"Last Entry\" value = \"0\"/>\n";
						$str .= "    <PARAM name = \"Generator\" value = \"CEPlayer\"/>\n";
						$cnt = 1;
						$needSpecEnd = 1;
					}
					$str .= "    <ENTRY>\n";
					$str .= "        <REF href=\"file://\\$prePath{$row['resource_link']}{$row['casename']}\"/>\n";
					$str .= "    </ENTRY>\n";
				}
			}
		}
		if ($needSpecEnd == 1){ // For Wince Codec
			$str .= "</ASX>";
		}
		return $str;
	}
}
?>
