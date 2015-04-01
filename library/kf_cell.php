<?php
require_once('const_def.php');
require_once('toolfactory.php');

class kf_cell{
	protected $params = array();
	protected $values = array();
	protected $strClass = '';
	protected $strProps = '';
	protected $multi_value = false;
	protected $multi_edit = false;
	function __construct($params, $values = array()){
		$this->init($params, $values);
	}
	
	protected function init($params, $values){
		$this->params = $params;
		$this->values = $values;
		$this->tool = toolFactory::get('kf');
		
		// print_r($params);
		if (empty($this->params['tag']))
			$this->params['tag'] = $params['type'];

		$props = $this->selectProps();

		$this->strProps = $this->propStr($props);
		if(!isset($this->params['class']))
			$this->params['class'] = array();
		$class = $this->params['class'];
		$this->strClass = implode(' ', $class);
	}
	
	function getParams(){
		return $this->params;
	}
	
	function display($display_status = DISPLAY_STATUS_EDIT){
		$value = $this->_getValue();
		$props = $this->selectProps();

		if(empty($this->params['editable']))
			$display_status = DISPLAY_STATUS_VIEW;
		if(($display_status == DISPLAY_STATUS_EDIT && $this->multi_edit) || $this->multi_value){
			$ret = $this->_interTable($value, $props, $display_status);
		}
		else{
			if(empty($props['style']))
				$props['style'] = 'width:100%;';
			else
				$props['style'] .= ';width:100%;';
			if($display_status == DISPLAY_STATUS_EDIT){
				$ret = $this->oneEdit($value, $props);
			}
			else{
				$ret = $this->oneView($value, $props);
			}
		}
		return $ret;
	}
	
	protected function _interTable($value, $props, $display_status){
		$count = 0;
		$data = array();
		if($display_status == DISPLAY_STATUS_EDIT){
			if(!empty($this->params['onlyshowchecked'])){
				foreach($this->params['value'] as $k=>$v){
					$data[$k] = $v;
				}
			}
			elseif(!empty($this->params['editoptions']['value'])){
				$data = $this->params['editoptions']['value'];
			}
		}
		else{
			if(!is_array($value))
				$value = array($value=>$value);
			$data = $value;
		}
		$count = count($data);

		$displayField = '';
		$ret = array();
		$cols = isset($this->params['cols']) ? $this->params['cols'] : 3;
		$currentCol = 0;
		$needFieldSet = false;
		if($count > 1 || ($display_status == DISPLAY_STATUS_EDIT && $this->params['type'] == 'cart')){
			$needFieldSet = true;
		}
		if($needFieldSet){//用fieldset围起来
			$ret[] = "<fieldset>";
			$ret[] = "<table style='width:100%'>";
		}
// print_r($data);		
		foreach($data as $k=>$v){
			// if(is_numeric($v)) //可能存在问题
				// $k = $v;
				
			if(empty($k))
				continue;
// print_r(">>>k = $k, v = $v<<<");
			if($needFieldSet){
				if($currentCol ++ == 0){
					$ret[] = "<tr>";
				}
				$ret[] = "<td>";
			}
			if($display_status == DISPLAY_STATUS_EDIT){
				$ret[] = $this->oneEdit($k, $props);
			}
			else{
				$ret[] = $this->oneView($k, $props);
			}
			if($needFieldSet){
				$ret[] = "</td>";
				if($currentCol == $cols){
					$ret[] = "</tr>";
					$currentCol = 0;
				}
			}
		}
		if($needFieldSet){
			if($currentCol != 0)
				$ret[] = "</tr>";
			$ret[] = "</table></fieldset>";
		}
		return implode("\n", $ret);	
	}
	
	function pre($display_status){
		$label = array('type'=>'label', 'id'=>$this->params['id'].'_label', 'value'=>$this->params['label'].':', 'class'=>$this->params['class']);
		$e = cellFactory::get($label);
		return $e->display(DISPLAY_STATUS_VIEW, true);
	}
	
	function post($display_status){
		$post = $this->params['post'];
		if(empty($post['type']))
			$post['type'] = 'text';
			
		if(!isset($post['value']))
			$post['value'] = '';
		if(!isset($post['title']))
			$post['title'] = '';
		if (empty($post['class']))
			$post['class'] = array('e-post');
		else
			$post['class'][] = 'e-post';
		$e = cellFactory::get($post);
		return $e->display($display_status, true);
	}

	protected function propStr($prop, $empty = true){
		$str = array();
		foreach($prop as $p=>$v){
			$str[] = $this->onePropStr($p, $v, $empty);
		}
		return implode(' ', $str);
	}
	
	protected function onePropStr($prop, $value, $empty = true){
		$str = "";
		
		if (isset($value) && ($empty || !empty($value))){
			if ($prop == 'name' && ($this->params['type'] == 'checkbox' || $this->params['type'] == 'cart'))
				$str = "name='{$value}[]'";
			elseif($prop == 'class' && is_array($value)){
				$str = "class='".implode(' ', $value)."'";
			}
			else if($prop == 'event'){ //直接生成javascript:func
				$str = $this->eventStr($value);
			}
			elseif($prop == 'note')
				$str = "title='".htmlentities($value)."'";
			// elseif($prop == 'checked'){
				// $values = $this->_getValue();
				// if($this->multi_value){
					// if(in_array($value, $values))
						// $str = "checked='checked'";
				// }
				// else if($value == $values)
					// $str = "checked='checked'";
			// }
			elseif(is_array($value)){
				$str = "$prop='".json_encode($value)."'";
			}
			else
				$str = "$prop='".htmlentities($value)."'";
		}
		return $str;
	}
	
	protected function eventStr($events){
		$ret = '';
		if(!empty($events)){
			//event = array('onclick'=>array('fun1', 'fun2'), 'onchange'=>array('fun1', 'fun2'))
			$str = array();
			foreach($events as $event=>$functions){
				$str[] = strtolower($event)."='javascript:";
				if(is_array($functions)){
					$es = array();
					foreach($functions as $f){//可能有需要实际值替换的
						$f = $this->tool->vsprintf($f, $this->params['value']);
						$es[] = $f;
					}
					$str[] = implode(';', $es);
				}
				else{//可能有需要实际值替换的
					$f = $this->tool->vsprintf($functions, $this->params['value']);
					$str[] = $f;
				}
				$str[] = "'";
			}
			$ret = implode(' ', $str);
		}
		return $ret;
	}
	
	protected function selectProps(){
		$props = $this->getProps();
		if(is_string($props))
			$props = explode(',', $props);
		$standard = array();
		foreach($props as $p=>$defaultValue){
			if(is_int($p)) {
				$p = $defaultValue;
				$defaultValue = null;
			}
			$standard[$p] = isset($this->params[$p]) ? $this->params[$p] : (isset($defaultValue) ? $defaultValue : null);
		}
		return $standard;
	}
	
	protected function getProps(){
		return array('type', 'id', 'name', 'ignored', 'unique', 'title', 'editable', 'value', 'placeholder', 'disabled', 'class', 'style', 'required', 'min', 'max', 'invalidchar', 'width', 'event', 'original_value');
	}
	
	protected function oneEdit($value, $props){
		$strProps = $this->propStr($props);
		$ret = "<{$this->params['tag']} $strProps >$value</{$this->params['tag']}>";
		return $ret;
	}
	
	protected function oneView($value, $props){
// print_r("value = $value\n");
// print_r($this->params['editoptions']);
		if(!empty($this->params['editoptions']['value'][$value]))
			$value = $this->params['editoptions']['value'][$value];
// if($this->params['name'] == 'prj_id'){
	// print_r($value);
	// print_r($this->params);
// }		
// print_r("value = $value\n");			
		$label = array('type'=>'label', 'value'=>$value, 'class'=>$this->params['class'], 'editoptions'=>isset($this->params['editoptions']) ? $this->params['editoptions'] : array());
// print_r("label = ");
// print_r($label['class']);
		$required_index = array_search('required', $label['class']);
		if($required_index !== false){
// print_r('required_index = '.$required_index);			
			unset($label['class'][$required_index]);
		}
		if(!empty($this->params['id']))
			$label['id'] = $this->params['id'];
		
		$e = cellFactory::get($label);
		return $e->display(DISPLAY_STATUS_VIEW, true);
	}
	
	protected function _getValue(){
		$value = isset($this->params['value']) ? $this->params['value'] : '';
		if($this->multi_value){
			if(is_string($value))
				$value = explode(',', $value);
			if(!is_array($value))
				$value = array($value);
// print_r($value);
			$newValues = array();
			foreach($value as $k=>$v){
				$newValues[$v] = $v;
			}
			$this->params['value'] = $value = $newValues;
// print_r($this->params['value']);				
		}
		return $value;	
	}
	

}
?>