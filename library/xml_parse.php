<?php
require_once('file_parse.php');

class xml_parse extends file_parse{
	protected function _parse(){
		$xml = json_decode(json_encode((array) simplexml_load_file($this->filename)), true);
		$this->parseXMLNode($xml);
		return ERROR_OK;
	}
	
	protected function parseXMLNode($xml){
// print_r($xml);	
		foreach($xml as $key=>$v){
// print_r("key = $key, v = ");
// print_r($v);
			$method = preg_replace('/[\s-=]/', '_', $key);
			$method = 'parse_'.$method;
			if (method_exists($this, $method)){
				$this->data['root'][$key] = $this->{$method}($v);
			}
			else{
				$this->data['root'][$key] = $this->default_parse_node($v);
			}
		}
	}
	
	protected function default_parse_node($v){
		return $v;
	}
}
?>
