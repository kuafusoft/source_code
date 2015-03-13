<?php
require_once('toolfactory.php');
require_once('exporter_base.php');

class exporter_txt extends exporter_base{
	protected $str = '';
	
	protected function init($params){
		parent::init($params);
		$this->fileName .= '.txt';
	}
	
	protected function save(){
		$tool = toolFactory::get('kf');
		$fileName = $tool->saveFile($this->str, $this->fileName);
		return $fileName;
	}
};

?>
