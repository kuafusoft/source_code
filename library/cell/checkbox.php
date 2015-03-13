<?php
require_once('kf_cell.php');

class kf_checkbox extends kf_cell{
	protected function init($params, $values){
		parent::init($params, $values);
		$this->multi_value = true;
		$this->multi_edit = true;
		// $this->params['value'] = 
		$this->_getValue($params['value']);
		$this->params['onlyshowchecked'] = isset($params['onlyshowchecked']) ? $params['onlyshowchecked'] : false; //购物车模式只显示选中项
// print_r($this->params['value']);		
	}
	
	protected function oneView($v, $props){
		$ret = parent::oneView($v, $props);
// print_r($ret);
		return $ret;
	}
	
	protected function oneEdit($k, $props){
		unset($props['checked']);
		$props['id'] = "{$this->params['id']}_{$k}";
		$props['value'] = $k;
		
		if($this->multi_value){
			if(in_array($k, $this->params['value'])){
				$props['checked'] = "checked";
				$props['original_value'] = 1;
			}
			elseif($this->params['onlyshowchecked'])
				return '';
			else
				$props['original_value'] = 0;
		}
		elseif($k == $this->params['value']) // for radio
			$props['checked'] = "checked";
		
		$ret = '';
		$v = $this->params['editoptions']['value'][$k];
		if(is_array($v)){
			$label = isset($v['label']) ? $v['label'] : (isset($v['value']) ? $v['value'] : (isset($v['id']) ? $v['id'] : '[unknown]'));
			$props = $this->tool->array_extends($props, $v);
		}
		else{
			$label = $v;
		}
		$strProps = $this->propStr($props, false);
		$ret = "<label for='{$props['id']}'>{$label}<input {$strProps}></label>";
		return $ret;
	}
	
	protected function getProps(){
		$props = parent::getProps();
		$props[] = 'single_multi';
		return $props;
		return array('type', 'id', 'name', 'ignored', 'unique', 'title', 'editable', 'value', 'placeholder', 'disabled', 'class', 'style', 'required', 'min', 'max', 'invalidchar', 'width', 'event', 'original_value');
	}
	
}
?>