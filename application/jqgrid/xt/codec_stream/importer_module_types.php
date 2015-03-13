<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_module_types extends importer_excel{
	protected $total = 0;
	private $types;
	
	protected function process(){
		$res = $this->db->query("select id, element_ids from tag where name = 'Linux' and db_table = 'xt.codec_stream'");
		if($info = $res->fetch())
			$linux = $info['element_ids'];
		$this->types['linux'] = explode(",", $linux);
print_r("linux:".count($this->types['linux'])."\n<BR />");
		parent::process();
		print_r("total = ".$this->total);
		print_r("linux:".count($this->types['linux'])."\n<BR />");
		$this->db->update("tag", array('element_ids'=>implode(",", $this->types['linux'])), 'id='.$info['id']);
	}
	
	protected function processSheetData($title, $sheet_data){
		$total = 0;
		foreach($sheet_data as $stream){
			$this->processStream($stream, $title);
			$total ++;
		}
print_r("sheet $title, total = $total\n");		
	}
	
	protected function processStream($stream, $title){
		if($stream['codec_stream_type'] == 'case' || $stream['codec_stream_type'] == 'No linux'){
			$codec_stream_format_id = $this->getId('codec_stream_format', array('name'=>$stream['codec_stream_format']), array('name'));
			if($codec_stream_format_id == 'error')
print_r("codec_stream_format:".$stream['codec_stream_format']."\n<BR />");
			else{
				$res = $this->db->query("select codec_stream.id as codec_stream_id from codec_stream".
					" left join codec_stream_type on codec_stream_type.id = codec_stream.codec_stream_type_id".
					" where codec_stream.isactive = 1 and codec_stream.codec_stream_format_id = ".$codec_stream_format_id);
				while($row = $res->fetch()){
					if(in_array($row['codec_stream_id'], $this->types['linux'])){
						$k = array_search($row['codec_stream_id'], $this->types['linux']);
// print_r($k."\n<BR />");
						if($k !== false)}{
							unset($this->types['linux'][$k]);
							// if(!isset($this->typse[$codec_stream_format_id])){
								// //$this->types[$codec_stream_format_id][$row['codec_stream_type_id']] = $row['codec_stream_type_id'];
								// $this->types[$stream['codec_stream_format']][$row['codec_stream_type']] = $row['codec_stream_type'];
							// }
						}
					}
				}
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
