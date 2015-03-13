<?php
defined("DISPLAY_STATUS_VIEW") || define("DISPLAY_STATUS_VIEW", 1);
defined("DISPLAY_STATUS_EDIT") || define("DISPLAY_STATUS_EDIT", 2);
defined("DISPLAY_STATUS_NEW") || define("DISPLAY_STATUS_NEW", 3);
defined("DISPLAY_STATUS_QUERY") || define("DISPLAY_STATUS_QUERY", 4);

class kf_form(){
	protected $elements = array();
	protected $value = array();
	protected $display_status = DISPLAY_STATUS_VIEW;
	protected $tool = null;
	function __construct($elements, $value = array(), $display_status = DISPLAY_STATUS_VIEW){
		$this->init($elements, $value, $display_status);
	}
	
	function init($elements, $value, $display_status){
		$this->display_status = $display_status;
		$this->value = $value;
		$this->tool = toolFactory::get('kf');
		foreach($elements as $k=>$cell){
			$this->elements[$k] = $this->model2e($cell);
		}
	}
	
	function model2e($model){
		$p = array('required'=>false, 'cols'=>4, 'rows'=>3, 'colspan'=>1, 'init_type'=>'single', 
			'limit'=>'', 'displayField'=>'', 
			'editoptions'=>array(), 'queryoptions'=>array(), 'searchoptions'=>array(),
			'name'=>'', 'id'=>'', 'label'=>'', 'editable'=>true, 'type'=>'', 'unique'=>false,
			'post'=>array(), 'class'=>array(), 'placeholder'=>'', 'DATA_TYPE'=>'varchar', 'invalidChar'=>'',
			'force_readonly'=>false, 'ignored'=>false, 
			'temp'=>array(), 'legend'=>'', 'prefix'=>'', 'data_source_db'=>'', 'data_source_table'=>'',
			'cart_db'=>'', 'cart_table'=>'', 'cart_data'=>array(),
			'value'=>''
			);
		$e = $this->tool->array_extends($model, $p);
		if(empty($e['id']) && !empty($e['name']))
			$e['id'] = $e['name'];
		if(empty($e['name']) && !empty($e['id']))
			$e['name'] = $e['id'];
		if(empty($e['required']))
			$e['required'] = isset($model['editrules']['required']) ? $model['editrules']['required'] : false,
		if(empty($e['prefix']) && !empty($e['id']))
			$e['prefix'] = $e['id'];
		if($this->display_status == DISPLAY_STATUS_NEW && !empty($e['addoptions']))
			$e['editoptions'] = $e['addoptions'];
		elseif($this->display_status == DISPLAY_STATUS_QUERY && !empty($e['searchoptions']))
			$e['editoptions'] = $e['searchoptions'];
		if (empty($e['type'])){
			if($this->display_status == DISPLAY_STATUS_QUERY && !empty($model['queryoptions']['querytype']))
				$e['type'] = $model['queryoptions']['querytype'];
			else if (!empty($model['stype']))
				$e['type'] = $model['stype'];
			else if (!empty($model['edittype']))
				$e['type'] = $model['edittype'];
			else
				$e['type'] = 'text';
		}
		switch($e['type']){
			case 'textarea':
				if($this->display_status == DISPLAY_STATUS_QUERY)
					$e['type'] = 'text';
				break;
			case 'select':
				$e['value'] = 0;
				$cc = count($e['editoptions']['value']);
				if(!empty($e['editoptions']['multiple'])){
					if($cc < 10 || empty($e['cart_db']) || empty($e['cart_table']))
						$e['type'] = 'checkbox';
					else
						$e['type'] = 'cart';
				}
				elseif($cc < 5){
					$e['type'] = 'radio';
				}
				break;
			case 'single_multi':
				if($e['init_type'] == 'single'){
					$e['type'] = 'select';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'+', 'id'=>'single_to_multi', 'title'=>'Change to multe-selction');
					$e['value'] = 0
				}
				else{
					$e['type'] = 'cart';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'-', 'id'=>'multi_to_single', 'title'=>'Change to single selection');
				}
				$e['single_multi']['options'] = $e['editoptions'];
				break;
			case 'multi_row_edit':
			case 'embed_table':
				$e['editable'] = true;
				break;
			
		}

		if ($editable == false || !$e['editable'] || !empty($model['readonly']))
			$e['readonly'] = 'readonly';
		if (isset($this->value[$e['name']])){
			$e['value'] = $this->value[$e['name']];
		}
		elseif(!empty($this->value[$e['id']])){
			$e['value'] = $this->value[$e['id']];
		}
		elseif(isset($e['defval']))
			$e['value'] = $e['defval'];

		if(!is_array($e['value']))
			$e['value'] = htmlentities($e['value'], ENT_QUOTES);
			
		$e['original_value'] = $e['value'];
		if ($this->display_status == DISPLAY_STATUS_QUERY){
			$e['unique'] = false;
			$e['required'] = false;
			if (!empty($e['force_readonly'])){
				$e['editable'] = false;
				$e['readonly'] = true;
			}
			else{
				$e['editable'] = true;
				$e['readonly'] = false;
			}
		}
		if(!empty($e['unique']))
			$e['class'][] = 'unique';
		if(!empty($e['required']))
			$e['class'][] = 'required';
			
		return $e;
	}
	
	function display($colsInRow = 1){
		$hidden = array("<table id='hidden_elements'><tr>");
		$normal = array("<table id='normal_elements' class='ces'>");
		$currentCol = 0;
		foreach($this->elements as $k=>$cell){
			if($cell['type'] == 'hidden'){
				$hidden[] = "<td><input type='hidden' name='{$cell['name']}' id='{$cell['id']}' value='{$cell['value']}'></td>";
			}
			else{
				if($currentCol == 0)
					$normal[] = "<tr>";
				$e = new kf_cell($cell);
				$aaa = $e->display($this->display_status);
				foreach($aaa as $a)
					$normal[] = $a;
				$currentCol ++;
				if($currentCol == $colsInRow){
					$normal[] = "</tr>";
					$currentCol = 0;
				}
			}
		}
		$hidden[] = "</tr></table>";
		$normal[] = '</table>';
		return implode("\n", $hidden).implode("\n", $normal);
	}
}

class kf_cell{
	protected $params = array();
	protected $props = array();
	protected $propStr = array();
	function __construct($type, $params){
		$this->init($type, $params);
	}
	
	protected function init($type, $params){
		$this->params = $params;
		$this->params['type'] = $type;
		if (empty($this->params['id']) && !empty($this->params['name']))
			$this->params['id'] = $this->params['name'];
		elseif (empty($this->params['name']) && !empty($this->params['id']))
			$this->params['name'] = $this->params['id'];
		if (empty($this->params['tag']))
			$this->params['tag'] = $type;
		if (!isset($this->params['editable']))
			$this->params['editable'] = true;
		if (empty($this->params['label']))
			$this->params['label'] = ucfirst($this->params['name']);
		if (!empty($this->params['value']))
			$this->params['original_value'] = $this->params['value'];
		else
			$this->params['original_value'] = '';
	}
	
	function display($display_status = DISPLAY_STATUS_EDIT){
		$editable = '';
		$hiddenProp = '';
		$this->selectProps();
		$str = $this->propStr($this->props);
		$this->propStr = implode(' ', $str);
		
		if($this->params['editable']){
			$editable = "editable='editable'";
			// if($cellStatus == DISPLAY_STATUS_VIEW)
				$hiddenProp = "hiddenProp='".json_encode($this->params)."'";
print_r($this->params)				;
		}
		$str = array();
		$str[] = $this->displayPre($cellStatus);
		if (!empty($this->params['post'])){
			$str[] = "<td><table style='width:100%'><tr style='width:100%'>";
		}
		$str[] = "<td id='td_{$this->params['name']}' class='e-con' $editable $hiddenProp>";
		if($cellStatus == DISPLAY_STATUS_EDIT && $this->params['editable'])
			$str[] = $this->_edit();
		else{
			$str[] = $this->_view();
		}
		$str[] = "</td>";
		if (!empty($this->params['post'])){
			$str[] = "<td class='e-post'>";
			$str[] = $this->displayPost($cellStatus);
			$str[] = "</td>";
			$str[] = "</tr></table></td>";
		}
// print_r($str)		;
		return implode('', $str);
	}
	
	protected function displayPre($cellStatus){
		$str = array();
		$str[] = "<td id='td_label_{$this->params['name']}' class='e-pre' style='text-align:right;'><span id='label_{$this->params['name']}'>{$this->params['label']}</span>";
		$display = ($cellStatus == DISPLAY_STATUS_EDIT) ? '' : "display:none";
		if (!empty($this->params['unique'])){
			$img_src = '/img/aHelp.png';
			$str[] = "<img id='img_unique_check' width='18' height='18' style='$display' edit_show='edit_show' src='$img_src'>";
		}
		if (!empty($this->params['required']))
			$str[] = "<span style='color:red;$display' edit_show='edit_show'>*</span>";
		$str[] = ":</td>";
		return implode('', $str);
	}
	
	protected function displayPost($cellStatus){
		$post = $this->params['post'];
		$type = isset($post['type']) ? $post['type'] : 'text';
		if(!isset($post['value']))
			$post['value'] = '';
		if(!isset($post['title']))
			$post['title'] = '';
		if (empty($post['class']))
			$post['class'] = 'e-post';
		else
			$post['class'] .= ' e-post';
		switch($type){
			case 'button':
				$strPost = "<button type='button' value='{$post['value']}' id='{$post['id']}' title='{$post['title']}'>{$post['value']}</button>";
				break;
			case 'text':
				$strPost = "<span class='{$post['class']}' title='{$post['title']}'>{$post['value']}</span>";
		}
		return $strPost;
	}
	
	protected function propStr($prop, $empty = true){
		$str = array();
		if(is_string($prop))
			$prop = explode(',', $prop);
		foreach($prop as $p=>$defaultValue){
			if(is_int($p)) {
				$p = $defaultValue;
				$defaultValue = null;
			}
			$str[] = $this->onePropStr($p, $defaultValue, $empty);
		}
		return $str;
	}
	
	protected function onePropStr($prop, $defaultValue, $empty = true){
		$str = "";
		$value = isset($this->params[$prop]) ? $this->params[$prop] : (isset($defaultValue) ? $defaultValue : null);
		
		if (isset($value) && ($empty || !empty($value))){
			if ($prop == 'name' && ($this->params['type'] == 'checkbox' || $this->params['type'] == 'cart'))
				$str = " name='{$value}[]'";
			elseif(is_array($value)){
				$str = " $prop='".json_encode($value)."'";
			}
			else
				$str = " $prop='$value'";
		}
		return $str;
	}
	
	protected function selectProps(){
// print_r($this->params)	;
		if (!isset($this->params['style']))
			$this->params['style'] = 'width:100%;';
		else
			$this->params['style'] .= ';width:100%';
		$defaultProps = array('type', 'id', 'name', 'unique', 'title', 'editable', 'value', 'disabled', 'class', 'style', 'required', 'width', 'original_value');
		$specialProps = $this->specialProps();
		$this->props = array_merge($defaultProps, $specialProps);
	}
	
	protected function specialProps(){
		return array();
	}
	
	protected function _edit(){
		unset($this->params['readonly']);
		$val = $this->getViewLabel();
		return "<{$this->params['tag']} {$this->propStr} onblur='XT.checkElement(this)'>{$val}</{$this->params['tag']}>";
	}
	
	protected function _view(){
		$label = $this->getViewLabel();
		$strOnClick = '';
		if($this->params['editable']){
			$strOnClick = " ondblclick='XT.click2edit(this, \"{$this->params['name']}\")'";
		}
		return "<label $strOnClick >$label</label>";
	}
	
	protected function getViewLabel(){
		return isset($this->params['value']) ? $this->params['value'] : '';
	}
}
?>