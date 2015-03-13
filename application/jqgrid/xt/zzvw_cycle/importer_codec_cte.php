<?php
require_once('importer_excel.php');

class xt_zzvw_cycle_importer_codec_cte extends importer_excel{
	protected $total = 0;
	
	protected function process(){
		if(!empty($this->parse_result)){
			$auto = $update_auto = 0;	
			foreach($this->parse_result as $data){
				foreach($data as $caseInfo){
					if(strtolower($caseInfo['result']) == 'pass' ){
						$testcase_id = $this->getId('testcase', array('code'=>trim($caseInfo['code'])), array('code'));//不更新
						// $res = $this->tool->query("select testcase_ver_id from prj_testcase_ver where prj_id = ".$prj_id." AND testcase_id = ".$testcase_id);
						// if($info = $res->fetch()){
							// $testcase_ver_id = $info['testcase_ver_id']
						// }
						if($testcase_id == 'error')
							continue;
						$result_type_id = $this->getResultId($caseInfo['result']);
						if('error' == $result_type_id || 0 == $result_type_id)
							continue;
						$cond = "cycle_id = {$this->params['id']} AND testcase_id = {$testcase_id}".
								" AND test_env_id = {$this->params['test_env_id']} AND codec_stream_id = 0".
								
						$update = array('result_type_id'=>$result_type_id, 'comment'=>$caseInfo['comment'], 'finish_time'=>date('Y-m-d H:i:s'));
						$res = $this->tool->query("select id, result_type_id, comment from cycle_detail where {$cond}");
						if($row = $res->fetch()){
							if(0 == $row['result_type_id']){
								$this->tool->update('cycle_detail', $update, "id=".$row['id']);
								$this->updatelastresult($testcase_id, $result_type_id, date('Y-m-d H:i:s'));
								$auto ++;
							}
							else {
									$update_auto ++;
							}
						}
					}
				}
			}
			if(!empty($auto))
print_r($auto." cases have updated at first time"."\n<br />");
			if(!empty($update_auto))
print_r($update_auto." cases have been update"."\n<br />" );
		}
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
			case 'ongoing':
				$result = 'ongoing';
				break;
		}
		if (stripos($result, 'not support') !== false)
			$result = 'N/S';
		
		return $this->getId('result_type', array('name'=>$result));
	}
	
	private function getId($table, $valuePair, $keyFields = array(), &$is_new = true){
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
	
	//cte: codec_stream_id = 0
	protected function updatelastresult($testcase_id, $result, $finish_time, $codec_stream_id = 0){		
		$res = $this->tool->query("select detail.prj_id, detail.compiler_id, detail.build_target_id, cycle.rel_id, detail.id from cycle_detail detail".
			" left join cycle on detail.cycle_id = cycle.id where cycle.id={$this->params['id']}".
			" and detail.testcase_id = {$testcase_id} and detail.codec_stream_id={$codec_stream_id}".
			" and detail.prj_id in (".implode(",", $this->params['prj_ids']).")".
			" and detail.compiler_id in (".implode(",", $this->params['compiler_ids']).")".
			" and detail.build_target_id in (".implode(",", $this->params['build_target_ids']).")");
		while($row = $res->fetch()){
			//cycle_detail_id是唯一的
			$tcres = $this->tool->query("SELECT id FROM testcase_last_result WHERE testcase_id={$testcase_id}".
				" AND prj_id={$row['prj_id']} AND rel_id={$row['rel_id']} AND codec_stream_id={$codec_stream_id}");
			if($data = $tcres->fetch()){
				$update = array('result_type_id'=>$result, 'cycle_detail_id'=>$row['id'], 'tested'=>$finish_time);
				$this->tool->update('testcase_last_result', $update, "id=".$data['id']);
			}
			else{
				$insert = array('testcase_id'=>'testcase_id', 'cycle_detail_id'=>$row['id'], 'result_type_id'=>$result,
					'rel_id'=>$row['rel_id'], 'tested'=>$finish_time, 'codec_stream_id'=>$codec_stream_id, 'prj_id'=>$row['prj_id']);//, 'compiler_id'=>$row['compiler_id'], 'build_target_id'=>$row['build_target_id']);
				$this->tool->insert('testcase_last_result', $insert);
			}
			$this->tool->update('testcase', array('last_run'=>$finish_time), "id=".$testcase_id);
		}			
	}
};

?>
