<?php
require_once('kf_cell.php');

class kf_text extends kf_cell{
	protected function oneEdit($value, $props){
		if($props['date'] == 'date'){
			//需要增加一个click的事件
			if(empty($props['event']['onclick']))
				$props['event']['onclick'] = array();
			$props['event']['onclick'][] = 'XT.datePick(this)';
		}
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
		$props[] = 'date';
		return $props;
	}
	
}
?>