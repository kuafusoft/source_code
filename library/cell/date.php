<?php
require_once('kf_cell.php');

class kf_date extends kf_cell{
	protected function oneEdit($value, $props){
		//需要增加一个click的事件
		if(empty($props['event']['onclick']))
			$props['event']['onclick'] = array();
		$props['event']['onclick'][] = 'XT.datePick(this)';
		$strProps = $this->propStr($props);
		$ret = "<input {$strProps} >";
		return $ret;
	}
}
?>