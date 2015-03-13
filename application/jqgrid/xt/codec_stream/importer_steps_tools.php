<?php
require_once('importer_excel.php');

class xt_codec_stream_importer_steps_tools extends importer_excel{
	protected $total = 0;
	private $rulesInfo;
	
	protected function default_steps_tools($sheet, $title){
		return $this->default_analyze_sheet($sheet, $title);
	}
	protected function default_format_tools($sheet, $title){
		return $this->default_analyze_sheet($sheet, $title);
	}
	protected function process(){
		parent::process();
		print_r("total = ".$this->total);
	}
	
	protected function processSheetData($title, $sheet_data){
		$total = 0;
// print_r($sheet_data);
		foreach($sheet_data as $result){
// print_r($result);
// print_r("\n<BR />");
// break;
			if(isset($result['steps']))
				$this->processSteps($result);
			else
				$this->processTools($result);
			$total ++;
		}
	}
	private function processSteps($result){
		$codec_stream_type_id = $this->getId("codec_stream_type", array('name'=>trim($result['codec_stream_type'])), array('name'));
		if($codec_stream_type_id == "error")
			return;
		$test_env_item_id = $this->getId("env_item", array('code'=>trim($result['test_env_item'])), array('code'));
		if($test_env_item_id == "error")
			return;
		$data = array('codec_stream_type_id'=>$codec_stream_type_id, 'env_item_id'=>$test_env_item_id, 'steps'=>$result['steps'],
			'precondition'=>$result['environment'], 'command'=>$result['command']);
		$this->db->insert("stream_steps", $data);
	}
	private function processTools($result){
print_r($result);
print_r("\n<BR />");
		if(strtolower($result['codec_stream_type']) != 'unknown'){
			//$codec_stream_type_id = $this->getId("codec_stream_type", array('name'=>$result['codec_stream_type']), array('name'));
			$codec_stream_format_id = $this->getId("codec_stream_format", array('name'=>trim($result['codec_stream_format'])), array('name'));
			if($codec_stream_format_id == "error")
				return;
			$test_env_item_id = $this->getId("env_item", array('code'=>trim($result['test_env_item'])), array('code'));
			if($test_env_item_id == "error")
				return;
			$data = array('codec_stream_format_id'=>$codec_stream_format_id, 'env_item_id'=>$test_env_item_id, 'os'=>$result['os']);
			$this->db->insert("stream_tools", $data);
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