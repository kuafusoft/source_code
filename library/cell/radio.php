<?php
require_once('kf_cell.php');

class kf_radio extends kf_cell{
	protected function init($params, $values){
		parent::init($params, $values);
		$this->multi_edit = true;
		$this->multi_value = false;
	}
	
	protected function oneEdit($k, $props){
		$ret = '';
		unset($props['checked']);
		$props['id'] = "{$this->params['id']}_{$k}";
		if($k == $this->params['value'])
			$props['checked'] = "checked";
		$v = $this->params['editoptions']['value'][$k];
		$props['value'] = $k;
		if(is_array($v)){
			$label = isset($v['label']) ? $v['label'] : (isset($v['value']) ? $v['value'] : (isset($v['id']) ? $v['id'] : '[unknown]'));
			// $label = $v[$displayField];
			$props = $this->tool->array_extends($v, $props);
		}
		else{
			$label = $v;
		}
// print_r($props);		
		$strProps = $this->propStr($props, false);
		$ret = "<label for='{$props['id']}'><input {$strProps}>{$label}</label>";
		return $ret;
	}
}
?>