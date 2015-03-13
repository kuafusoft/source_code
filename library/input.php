<?php
require_once('cell.php');

class input extends cell{
	protected function init($type, $params){
		parent::init($type, $params);
		$this->params['tag'] = 'input';
	}
	
	protected function _edit(){
		$str = "<input {$this->propStr}>";
		return $str;
	}
}
?>