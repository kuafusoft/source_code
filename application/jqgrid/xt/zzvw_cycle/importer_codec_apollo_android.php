<?php
require_once('importer_base.php');

class xt_zzvw_cycle_importer_codec_apollo_android extends importer_base{
	protected function parse($fileName){
		$parser = xml_parser_create();
		if (!($fp = fopen($fileName, "r"))) {
			die("could not open XML input");
		}
		if($data = fread($fp, filesize($fileName)))
		   xml_parse_into_struct($parser,$data,$vals,$index);

		xml_parser_free($parser);
		$i = 0;
		foreach($vals as $key=>$val){
// print_r($val);
// print_r("\n<BR />");
			$val['tag'] = strtolower(trim($val['tag']));
			if(!empty($val['tag'])){
				if($val['tag'] == 'testcase'){
					if(empty($val['attributes']))
						continue;
					$i++;
					$this->parse_result[$i]['codec_stream'] = trim($val['attributes']['ID']);
					$this->parse_result[$i]['stream']['result'] = strtolower(trim($val['attributes']['RESULT']));//'Sucess', 'Failure', 'No Test';
				}
				else if(($val['tag'] == 'failedscene')){
					$this->parse_result[$i]['comment'] = trim($val['attributes']['MESSAGE']);
				}
				else if($val['tag'] == 'trickmode'){
					if(empty($val['attributes']))
						continue;
					$this->parse_result[$i]['tm']['result'] =  strtolower(trim($val['attributes']['RESULT']));
				}
				else if($val['tag'] == 'operation'){
					if(empty($val['attributes']))
						continue;
					$val['attributes']['TAG'] = trim($val['attributes']['TAG']);
					if($val['attributes']['TAG'] == 'open' || $val['attributes']['TAG'] == 'stop'  || $val['attributes']['TAG'] == 'release')
						continue;
					$this->parse_result[$i]['trickmodes'][strtolower($val['attributes']['TAG'])] = strtolower(trim($val['attributes']['RESULT']));
// print_r($this->parse_result[$i]['trickmodes']);
// print_r("\n<BR />");									
				}
			}
		}
	}
	
	protected function process(){
		$auto = $update_auto = 0;
		$stream = $updateStreams = array();
		foreach($this->parse_result as $k=>$data){
// print_r($data);
// print_r("\n<BR />");
			if(empty($data['codec_stream']))
				continue;
			$codec_stream_id = $this->getId("codec_stream", array('code'=>$data['codec_stream']), array('code'));
			if($codec_stream_id == 'error')
				continue;
			$tm_res = array();
			if(empty($data['trickmodes']))
				continue;
			$stream_failure = false;

			if($data['stream']['result'] == 'failure'){
				$trickmodes_res = array_unique($data['trickmodes']);
				if(count($trickmodes_res) == 1 && in_array('success', $trickmodes_res)){
// print_r($data['codec_stream']."\n<BR />");
// print_r('total fail'."\n<BR />");
					$stream_failure = true;
				}
			}
			foreach($data['trickmodes'] as $tm=>$res){
				switch($tm){
					case 'play':
					case 'playuntilend':
						$tm = 'Android_Playback';
						$tm_res[$tm][] = $res;
						break;
					case 'seek':
						$tm = 'Android_Seek';
						$tm_res[$tm][] = $res;
						break;
					case 'pause':
					case 'resume':
						$tm = 'Android_Pause_Resume';
						$tm_res[$tm][] = $res;
						break;
				}
			}
			if(empty($tm_res[$tm]))
				continue;
			foreach($tm_res as $tm=>$res){
				$finish_time = 0;
				$result_type_id = 0;
				$testcase_id = $this->getId("testcase", array('code'=>$tm), array('code'));
				if($tm == 'error')
					continue;
				if(in_array('not test', $res))//not test
					continue;
				if($stream_failure || in_array('failure', $res)){// failure
// print_r($data['comment']."\n<BR />");
					if(empty($data['comment']))
						continue;
					$update = array('comment'=>$data['comment']."----update by apollo");
				}
				else{
					$result_type_id = $this->getResultId('pass');// pass
					if('error' == $result_type_id || 0 == $result_type_id)
						continue;
					$finish_time = date('Y-m-d H:i:s');
					$update = array('result_type_id'=>$result_type_id, 'finish_time'=>$finish_time, 'comment'=>'update by apollo');
				}
				$cond ="cycle_id = {$this->params['id']} and testcase_id = {$testcase_id}".
						" and codec_stream_id = {$codec_stream_id}";

				$result = $this->tool->query("select * from cycle_detail where $cond");
				if($row = $result->fetch()){
					if(0 == $row['result_type_id']){
						$this->tool->update("cycle_detail", $update, "id=".$row['id']);
						$this->updatelastresult($testcase_id, $this->params['id'], $result_type_id, $finish_time, $codec_stream_id);
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
		if(!empty($auto))
print_r($auto." streams have updated"."\n<br />");
		if(!empty($update_auto))
print_r($update_auto." streams have been update"."\n<br />" );
	}
	
	protected function getResultId($result){
		$result = strtolower($result);
		switch(strtolower($result)){
			case 'ok':
			case 'pass':
			case 'success':
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
};

?>
