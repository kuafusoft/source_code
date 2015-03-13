<?php
require_once('kf_cell.php');

class kf_text extends kf_cell{
	protected function oneEdit($value, $props){
		$strProps = $this->propStr($props);
		$ret = "<input {$strProps} >";
		return $ret;
	}

	protected function oneView($value, $props){
		// $value = $this->tool->insertLink($value);
		return parent::oneView($value, $props);
	}
	
	protected function getProps(){
		$props = parent::getProps();
		$props[] = 'auto_complete';
		$props[] = 'db';
		$props[] = 'table';
		$props[] = 'real_id';
		return $props;
	}
	
}
?>