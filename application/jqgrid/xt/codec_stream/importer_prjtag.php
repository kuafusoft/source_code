<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/codec_stream/importer_codec_stream_byxt.php');

class xt_codec_stream_importer_prjtag extends xt_codec_stream_importer_codec_stream_byxt{
	protected function processSheetData($title, $sheet_data){
		$total = 0;
		$res = $this->db->query("select id, code from codec_stream");
		while($row = $res->fetch()){
			$this->db->update("codec_stream", array('code'=>trim($row['code'])), "id=".$row['id']);
		}
		foreach($sheet_data as $stream){
			$this->processStream($stream, $title);
			$total ++;
		}
print_r("sheet $title, total = $total\n");	
// print_r($this->tags);	
		if(!empty($this->tags)){
			$element_ids = '';
			foreach($this->tags as $os=>$info){
				foreach($info as $pf=>$v){
					$element_ids = implode(',', $v);
// print_r($pf."\n<BR />");
					if(stripos($pf, ";")){
						$platform = explode(";", $pf) ;
// print_r($platform);
						// foreach($platform as $p){
							// $p = str_replace("6DQ", "6Q", $p);
							// $p = str_replace("6DL/S", "6DL", $p);
							// $p = str_replace("SabreSD", "SD", $p);
							// $name = $os."-".trim($p);
							// $name = str_replace(" ", "-", $name);
							// $data = array('name'=>$name, 'element_ids'=>$element_ids, 'db_table'=>'xt.codec_stream', 'id'=>'id', 
								// 'publish'=>1, 'creater_id'=>48, 'modified'=>date('Y-m-d'));
// // print_r($data);
							// $this->getElementId('tag', $data, array('name'));
						// }
						$data = array('name'=>$os, 'element_ids'=>$element_ids, 'db_table'=>'xt.codec_stream', 'id'=>'id', 
								'publish'=>1, 'creater_id'=>48, 'modified'=>date('Y-m-d'));
// print_r($data);
						$this->getElementId('tag', $data, array('name'));
					}
					else{
						$pf = str_replace("6DQ", "6Q", $pf);
						$pf = str_replace("6DL/S", "6DL", $pf);
						$pf = str_replace("SabreSD", "SD", $pf);
						$name = $os."-".trim($pf);
						$name = str_replace(" ", "-", $name);
						$data = array('name'=>$name, 'element_ids'=>$element_ids, 'db_table'=>'xt.codec_stream', 'id'=>'id', 
								'publish'=>1, 'creater_id'=>48, 'modified'=>date('Y-m-d'));
// print_r($data);
						$this->getElementId('tag', $data, array('name'));
					}
					// unset($element_ids);
				}
			}
			print_r('done');
			unset($this->tags);
		}
	}
	protected function processStream($stream, $title){
		$vp = $stream;
		if(isset($vp['os']) && isset($vp['platform'])){
			if($vp['os'] && $vp['platform'])
				$tag = true;
		}
// print_r("\n<BR />");
// print_r($vp);
// print_r("\n<BR />");
		$keys = array('code');
		if(!empty($vp['id']))
			$keys = array('id');
		$stream_id = $this->getId('codec_stream', $vp, $keys, $isNew);
if($stream_id == 'error')
print_r("\n<BR />".$vp['code']."\n<BR />");
		if($stream_id != 'error'){
			if(isset($tag) && $tag){
				if(isset($vp['os']) && isset($vp['platform'])){
					$this->tags[$vp['os']][$vp['platform']][$stream_id] = $stream_id;
				}
			}		
		}
		if (!$isNew){
			$this->total ++;
		}	
	}
	
	public function getId($table, $valuePair, $keyFields = array(), &$is_new = true){
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
			return "error";
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
}
?>
