<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_android_rules extends importer_excel{
	protected $total = 0;
	private $rulesInfo;
	
	protected function process(){
		parent::process();
		print_r("total = ".$this->total);
	}
	
	protected function processSheetData($title, $sheet_data){
		$total = 0;
// print_r($sheet_data);
		foreach($sheet_data as $result){
			$this->processRes($result, $title);
			$total ++;
		}
		$this->processRules();
	}
	
	protected function processRes($result, $title){
		static $info = array();
		$resInfo['codec_stream_id'] = $this->getId('codec_stream', array('code'=>trim($result['code'])), array('code'));
		if($resInfo['codec_stream_id'] == 'error'){
print_r("stream:".$result['code']."\n<BR />");
		}
		else{
			unset($result['code']);	
			foreach($result as $k=>$v){
				if($v == 'Y'){
					if(!isset($info[trim($k)])){
						$id = $this->getId('testcase', array('code'=>trim($k)), array('code'));
						if($id == 'error'){
	print_r("case:".$k."\n<BR />");
							continue;
						}
						$info[trim($k)] = $id;
					}
					$this->rulesInfo[$resInfo['codec_stream_id']][$info[trim($k)]] = $info[trim($k)]; 
				}
			}
		}
	}
	
	private function processRules(){
		foreach($this->rulesInfo as $streamid=>$data){
			$this->db->update("codec_stream", array('testcase_ids'=>implode(",", $data)), 'id='.$streamid);
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
			$res = $this->db->query("describe $table");
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
			$res = $this->db->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
			if ($row = $res->fetch()){
				$this->db->update($table, $realVP, "id=".$row['id']);
				$is_new = false;
				return $row['id'];
			}
			return 'error';
			// $is_new = true;
			// $this->db->insert($table, $realVP);
			// $element_id = $this->db->lastInsertId();
			// if ($cached)
				// $elements[$table][$keyField] = $element_id;
			// return $element_id;
		}
		$is_new = false;
		return $elements[$table][$valuePair[$keyField]];
	}
};

?>
