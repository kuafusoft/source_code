<?php
require_once('importer_base.php');

class xt_zzvw_cycle_importer_codec_apollo_gvb extends importer_base{
	protected $total = 0;
	
	protected function parse($fileName){
		$str = '';
		$handle = fopen($fileName, 'r');
		if ($handle){
			while(!feof($handle))
			  $data[] = fgets($handle);
			fclose($handle);
		}
		$i = 0;
		if(!empty($data)){
			for($row=0; $row<count($data); $row++){
				$row_data = trim($data[$row]);
				if(preg_match("/^TestStreamID:(.*)$/", $row_data, $matches)){
					$code = trim($matches[1]);
					$this->parse_result[$code]['code'] = $code;
				}
				if(!empty($code) && isset($this->parse_result[$code])){
					if(preg_match("/ActionGroup=(.*)$/", $row_data, $mc)){
						$info = explode(",", $mc[1]);
						$this->parse_result[$code][trim($info[0])] = trim($info[1]);
					}
					// else if(preg_match("/Total_Result](.*)$/", $row_data, $m))
						// $this->parse_result[$code]['exit'] = trim($m[1]);
				}
			}
		}
	}
	
	protected function process(){
		if(!empty($this->parse_result)){
// print_r($this->parse_result);
			$res = $this->tool->query("select prj_id from cycle where id = ".$this->params['id']);
			if($info = $res->fetch()){
				$prj_id = $info['prj_id'];
			}
			$trickmode = array('play', 'pause', 'accurate_seek', 'fast_seek', 'rotate');
			$auto = $update_auto = 0;
			$stream = $updateStreams = array();
			foreach($this->parse_result as $streamInfo){
				if(isset($streamInfo['code'])){
					$codec_stream_id = $this->getId('codec_stream', array('code'=>trim($streamInfo['code'])), array('code'));//不更新
					unset($streamInfo['code']);
					if(isset($streamInfo['name']))
						unset($streamInfo['name']);
					if(isset($streamInfo['location']))
						unset($streamInfo['location']);
					if(!empty($streamInfo['accurate_seek']) && !empty($streamInfo['fast_seek'])){
						if(strtolower($streamInfo['accurate_seek']) == 'pass' && strtolower($streamInfo['fast_seek']) == 'pass')
							$streamInfo['seek'] = $streamInfo['pause_seek'] =  'pass';//update
						else
							$streamInfo['seek'] = $streamInfo['pause_seek'] = 'fail';//不更新
						unset($streamInfo['accurate_seek']);
						unset($streamInfo['fast_seek']);
					}
// print_r($streamInfo);
					$case_type = 'Linux_';
					if(!empty($streamInfo['rotate']) && $streamInfo['rotate'] == 'na'){
						unset($streamInfo['rotate']);
					}
					if(!empty($streamInfo['play']))
						$streamInfo['exit'] = $streamInfo['play'];
					if(!empty($streamInfo['fffb']))
						$streamInfo['trick_Mode'] = $streamInfo['fffb'];
					foreach($streamInfo as $case=>$result){
						if(strtolower($result) == 'pass'){
							switch($case){
								case 'play':
									$case = $case_type.ucfirst($case)."back";
									break;
								case 'pause':
									$case = $case_type.ucfirst($case)."_Resume";
									break;
								case 'rotate':
									$case = $case_type.ucfirst($case);
									break;
								case 'seek':
									$case = $case_type.ucfirst($case);
									break;
								case 'pause_seek':
									$case = $case_type.ucfirst($case);
									break;
								case 'exit':
									$case = $case_type.ucfirst($case);
									break;
								case 'trick_Mode':
									$case = $case_type.ucfirst($case);
									break;
								default:
									break;
							}
// print_r($case."\n<BR />");
							$testcase_id = $this->getId('testcase', array('code'=>trim($case)), array('code'));//不更新
							// $res = $this->tool->query("select testcase_ver.id as testcase_ver_id from prj_testcase_ver".
							//	" left join testcase_ver on testcase_ver.id = prj_testcase_ver.testcase_ver_id".
							//	" where prj_testcase_ver.prj_id = ".$prj_id." AND prj_testcase_ver.testcase_id = ".$testcase_id.
							//	" and testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")");
							
							// if($info = $res->fetch()){
								// $testcase_ver_id = $info['testcase_ver_id']
							// }
							if($testcase_id == 'error')
								continue;
							$result_type_id = $this->getResultId($result);
							if('error' == $result_type_id || 0 == $result_type_id)
								continue;
							$cond = "cycle_id=".$this->params['id']." AND codec_stream_id = ".$codec_stream_id." AND testcase_id = ".$testcase_id.
								" AND test_env_id=".$this->params['test_env_id'];
							$update = array('result_type_id'=>$result_type_id, 'comment'=>'apollo gvb', 'finish_time'=>date('Y-m-d H:i:s'), 'tester_id'=>$this->params['owner_id']);
							
							$res = $this->tool->query("select * from cycle_detail where $cond");
							if($row = $res->fetch()){
								if(0 == $row['result_type_id']){
									$this->tool->update("cycle_detail", $update, "id=".$row['id']);
									$this->updatelastresult($testcase_id, $this->params['id'], $result_type_id, date('Y-m-d H:i:s'));
									if(!isset($stream[$codec_stream_id])){
										$auto ++;
										$stream[$codec_stream_id] = $codec_stream_id;
									}
								}
								else{
									if(!isset($stream[$codec_stream_id]) && !isset($updateStreams[$codec_stream_id])){
										$update_auto ++;
										$updateStreams[$codec_stream_id] = $codec_stream_id;
									}
								}
									
							}
						}
					}
				}
			}
		if(!empty($auto))
print_r($auto." streams have updated"."\n<br />");
		if(!empty($update_auto))
print_r($update_auto." streams have been update"."\n<br />" );
		}
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
			case 'ongoing':
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
		return $this->getId('result_type', array('name'=>$result));
	}
	
	protected function updatelastresult($testcase_id, $cycle_id, $result, $finish_time, $codec_stream_id = 0){
		$res = $this->tool->query("select cycle.prj_id, cycle.rel_id, detail.id from cycle left join cycle_detail detail on detail.cycle_id = cycle.id where cycle.id=".$cycle_id.
			" and detail.testcase_id = ".$testcase_id." and detail.codec_stream_id=".$codec_stream_id);
		if($row = $res->fetch()){
			$tcres = $this->tool->query("SELECT id FROM testcase_last_result WHERE testcase_id=".$testcase_id." AND prj_id=".$row['prj_id']." AND rel_id=".$row['rel_id']." AND codec_stream_id=".$codec_stream_id);
			if($data = $tcres->fetch())
				$this->tool->update('testcase_last_result', array('result_type_id'=>$result, 'cycle_detail_id'=>$row['id'], 'codec_stream_id'=>$codec_stream_id, 'tested'=>$finish_time), "id=".$data['id']);
			else
				$this->tool->insert('testcase_last_result', array('testcase_id'=>'testcase_id', 'cycle_detail_id'=>$row['id'], 'codec_stream_id'=>$codec_stream_id, 'result_type_id'=>$result, 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'tested'=>$finish_time));
			$this->tool->update('testcase', array('last_run'=>$finish_time), "id=".$testcase_id);
		}		
	}
	
	protected function getId($table, $valuePair, $keyFields = array(), &$is_new = true){
		static $elements = array();
		$cached = false;
		if (!empty($keyFields)){
			if(in_array('code', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'code')
						$keyField = $keyFields[$k];
				}
			}
			else if(in_array('name', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'name')
						$keyField = $keyFields[$k];
				}
			}
		}
		if (!$cached || empty($elements[$table][$valuePair[$keyField]])){
			$where = array();
			$realVP = array();
			$res = $this->tool->query("describe $table");
			while($row = $res->fetch()){
				if (isset($valuePair[$row['Field']]))
					$realVP[$row['Field']] = $valuePair[$row['Field']];
			}
// if($table == 'testcase_ver')
// print_r($realVP);
			if (empty($keyFields))
				$keyFields = array_keys($realVP);
			foreach($keyFields as $k){
				$where[] = "$k=:$k";
				$whereV[$k] = $realVP[$k];
			}
			$res = $this->tool->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
			if ($row = $res->fetch()){
				$this->tool->update($table, $realVP, "id=".$row['id']);
				$is_new = false;
				return $row['id'];
			}
			return 'error';
			// $is_new = true;
			// $this->tool->insert($table, $realVP);
			// $element_id = $this->tool->lastInsertId();
			// if ($cached)
				// $elements[$table][$keyField] = $element_id;
			// return $element_id;
		}
		$is_new = false;
		return $elements[$table][$valuePair[$keyField]];
	}
};

?>
