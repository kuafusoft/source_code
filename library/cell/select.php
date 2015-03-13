<?php
require_once('kf_cell.php');

class kf_select extends kf_cell{
	protected function oneEdit($value, $props){
// if($this->params['name'] == 'owner_id')	
// print_r($this->params);	
		$str = "<select {$this->strProps}>\n";
		if(!empty($this->params['editoptions']['value'])){
			foreach($this->params['editoptions']['value'] as $k=>$v){
				$str .= $this->displayOption($k, $v);
			}
		}
		$str .= "</select>";
		return $str;
	}
	
	protected function displayOption($k, $v){
		$props = array('value'=>$k);
		if(is_array($v)){
			$label = isset($v['label']) ? $v['label'] : (isset($v['value']) ? $v['value'] : (isset($v['id']) ? $v['id'] : '[unknown]'));
			$props = array_merge($props, $v);
		}
		else
			$label = $v;
// print_r($props);			
		$strProps = $this->propStr($props);
		$str = "<option $strProps>$label</option>\n";
		return $str;
	}
	
	protected function getProps(){
		$props = parent::getProps();
		$props['style'] = 'width:100%;';
		$props['single_multi'] = array();
		return $props;
	}
}
?>