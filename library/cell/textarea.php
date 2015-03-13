<?php
require_once('kf_cell.php');

class kf_textarea extends kf_cell{
	protected function _getValue(){
		$val = parent::_getValue();
		return $val;
	}
	
	protected function oneView($value, $props){
		// $value = $this->tool->insertLink($value);
		return parent::oneView($value, $props);
	}
}
?>