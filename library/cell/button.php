<?php
require_once('kf_cell.php');

class kf_button extends kf_cell{
	protected function init($params, $values){
		parent::init($params, $values);
		$this->params['editable'] = true;
	}
	
	protected function oneView($value, $props){
		return '';
	}
	
	protected function oneEdit($value, $props){
// print_r(">>>>>>>>>>>>>>>>>>>>>>>>>>>value = $value<<<<<<<<<<<<<<<<<<<<<<<<<<<\n");
		return "<button {$this->strProps}>$value</button>";
	}
}
?>