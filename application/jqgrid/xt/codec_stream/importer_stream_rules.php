<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_stream_rules extends importer_excel{
	protected $total = 0;
	private $rulesInfo;
	private $tag;
	private $note;
	
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
		// if(!empty($this->tag)){
			// $this->processTag();
		// }
		// if(!empty($this->note)){
			// $this->processNote();
		// }
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
				if(strtolower($v) == 'y'){
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
			if(isset($result['tag']) && $result['tag'] == 'Linux'){
				$this->tag['Linux'][] = $resInfo['codec_stream_id'];
			}
			if(isset($result['note'])){
				$this->note[$resInfo['codec_stream_id']] = $result['note'];
			}
		}
	}
	
	private function processRules(){
		foreach($this->rulesInfo as $streamid=>$data){
			if(!empty($data)){
				$this->db->query("update codec_stream set testcase_ids = concat(testcase_ids, ',".implode(",", $data)."') where id=".$streamid);
			}
		}
	}
	
	private function processTag(){
		foreach($this->tag as $tag=>$element_id){
			if($tag == 'Linux'){
print_r(count($element_id)."\n<BR />");
print_r("update tag set element_ids = '".implode(",", $element_id)."' where name='Linux'"."\n<BR />");
				$this->db->update("tag", array('element_ids'=> implode(",", $element_id)), "name='Linux'");
				$this->db->update("codec_stream", array('isactive'=>1), "id in (".implode(",", $element_id).")");
			}
		}
	}
	
	private function processNote(){
		foreach($this->note as $streamid=>$nt){
			if(!empty($nt)){
// print_r('update codec_stream set note = concat(note, '.mysql_real_escape_string($nt).') where id='.$streamid."\n<BR />");
				$nt = mysql_real_escape_string($nt);
				$this->db->update("codec_stream", array('note'=>$nt), "'id'=".$streamid);
			}
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
