<?php
class file_parse{
	protected $filename = '';
	protected $params = array();
	protected $handle = null;
	protected $data = array();
	protected $current = '';
	protected $currentLine = 0;
	
	public function __construct($filename, $params = array()){
		$this->init($filename, $params);
	}
	
	protected function init($filename, $params){
		$this->filename = $filename;
		if(!file_exists($this->filename))
			die('The file does not exist');
		$this->params = $params;
		$this->parseFileName();
	}
	
	public function parse(){
		return $this->_parse();
	}
	
	public function getData(){
		return $this->data;
	}
	
	protected function parseFileName(){
		$pathinfo = pathinfo($this->filename);
		$this->_parseFileName($pathinfo);
	}
	
	protected function _parseFileName($pathInfo){
	
	}
	
	protected function _parse(){
		return ERROR_OK;
	}
}
?>
