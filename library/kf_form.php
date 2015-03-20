<?php
require_once('const_def.php');
require_once('toolfactory.php');
require_once('cellfactory.php');

class kf_form{
	protected $elements = array();
	protected $value = array();
	protected $display_status = DISPLAY_STATUS_VIEW;
	protected $tool = null;
	function __construct($elements, $value = array(), $display_status = ''){
		$this->init($elements, $value, $display_status);
	}
	
	function init($elements, $value, $display_status){
// print_r("displayStatus = $display_status");		
		$this->display_status = empty($display_status) ? DISPLAY_STATUS_EDIT : $display_status;
		$this->value = $value;
		$this->tool = toolFactory::get('kf');
		foreach($elements as $k=>$cell){
			$this->elements[$k] = $this->model2e($cell);
		}
	}
	
	function model2e($model){
// if($model['name'] == 'owner_id')
// print_r($model);
		$p = array('required'=>false, 'cols'=>4, 'rows'=>3, 'colspan'=>1, 'init_type'=>'single', 
			'limit'=>'', 'displayField'=>'', 
			// 'addoptions'=>array(), 'editoptions'=>array(), 'queryoptions'=>array(), 'searchoptions'=>array(),
			'name'=>'', 'id'=>'', 'label'=>'', 'editable'=>true, 'type'=>'', 'unique'=>false,
			'post'=>array(), 'class'=>array(), 'placeholder'=>'', 'DATA_TYPE'=>'varchar', 'invalidChar'=>'', 'email'=>0,
			'force_readonly'=>false, 'ignored'=>false, 
			'temp'=>array(), 'legend'=>'', 'prefix'=>'', 'data_source_db'=>'', 'data_source_table'=>'',
			'cart_db'=>'', 'cart_table'=>'', 'cart_data'=>array(),
			);
		$e = $this->tool->array_extends($p, $model);
		if(empty($e['id']) && !empty($e['name']))
			$e['id'] = $e['name'];
		if(empty($e['name']) && !empty($e['id']))
			$e['name'] = $e['id'];
		if(empty($e['label']))
			$e['label'] = ucfirst($e['name']);
		if(empty($e['required']))
			$e['required'] = isset($model['editrules']['required']) ? $model['editrules']['required'] : false;
		if(empty($e['prefix']) && !empty($e['id']))
			$e['prefix'] = $e['id'];
			
		if($this->display_status == DISPLAY_STATUS_NEW && !empty($model['addoptions'])){
			$e['editoptions'] = $e['addoptions'];
		}
		elseif($this->display_status == DISPLAY_STATUS_QUERY && !empty($model['searchoptions'])){
			$e['editoptions'] = $model['searchoptions'];
		}
		else
			$e['editoptions'] = isset($model['editoptions']) ? $model['editoptions'] : array();
			
		if(!empty($e['editoptions']['value']) && is_string($e['editoptions']['value'])){
			$e['editoptions']['value'] = $this->tool->str2Array($e['editoptions']['value']);
		}
// if($model['name'] == 'owner_id')
// print_r($e);
		if (empty($e['type'])){
			if($this->display_status == DISPLAY_STATUS_QUERY && !empty($model['queryoptions']['querytype']))
				$e['type'] = $model['queryoptions']['querytype'];
			else if (!empty($model['edittype']))
				$e['type'] = $model['edittype'];
			else if (!empty($model['stype']))
				$e['type'] = $model['stype'];
			else
				$e['type'] = 'text';
		}
// if($model['name'] == 'owner_id')
// print_r(">>>type = {$e['type']}<<<");		
		switch($e['type']){
			case 'textarea':
				if($this->display_status == DISPLAY_STATUS_QUERY)
					$e['type'] = 'text';
				break;
			case 'select':
				if($this->display_status != DISPLAY_STATUS_QUERY){
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
				}
				break;
			case 'single_multi':
				if($e['init_type'] == 'single'){
					// $e['type'] = 'select';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'+', 'id'=>'single_to_multi', 'title'=>'Change to multe-selction', 
						'event'=>array('onclick'=>'XT.single_or_multi(this)'));
				}
				else{
					// $e['type'] = 'cart';
					$e['editoptions']['multiple'] = false;
					$e['editoptions']['size'] = 1;
					$e['post'] = array('type'=>'button', 'value'=>'-', 'id'=>'multi_to_single', 'title'=>'Change to single selection',
						'event'=>array('onclick'=>'XT.single_or_multi(this)'));
				}
				$e['single_multi'] = array('db'=>$e['cart_db'], 'table'=>$e['cart_table'], 'options'=>$e['editoptions']);
				break;
			case 'multi_row_edit':
			case 'embed_table':
				$e['editable'] = true;
				break;
			
		}

		if (!$e['editable'] || !empty($model['readonly']))
			$e['readonly'] = 'readonly';
			
		if (isset($this->value[$e['name']])){
			$e['value'] = $this->value[$e['name']];
		}
		elseif(isset($this->value[$e['id']])){
			$e['value'] = $this->value[$e['id']];
		}
		elseif(isset($model['value']))
			$e['value'] = $model['value'];
		elseif(isset($e['defval']))
			$e['value'] = $e['defval'];

		if(!isset($e['value']))
			$e['value'] = '';
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
		if(empty($e['required']))
			unset($e['required']);
		else
			$e['class'][] = 'required';

		if(empty($e['unique']))
			unset($e['unique']);
		else
			$e['class'][] = 'unique';
			
		// invalidChar
		if(empty($e['invalidChar'])){
			switch($e['DATA_TYPE']){
				case 'int':
					$e['invalidChar'] = '[^\d-]';
					if ($e['name'] == 'progress'){
						$e['min'] = "min='0'";
						$e['max'] = "max='100'";
					}
					break;
				case 'float':
				case 'double':
					$e['invalidChar'] = '[^0-9\.-]';
					break;
				
			}
		}
		if($e['name'] == 'email')
			$e['email'] = 1;
		if(empty($e['placeholder']))
			$e['placeholder'] = "Please input {$e['label']} here";
		if(!in_array($e['type'], array('text', 'textarea')))
			unset($e['placeholder']);
		//处理editoptions里的Value，主要是要将id和name转换名称
// print_r($e['editoptions']);		
		if(!empty($e['editoptions']['value'])){
			foreach($e['editoptions']['value'] as $k=>&$v){
				if(is_array($v)){
					$displayField = $this->tool->getDisplayField($v);
					if($displayField != 'id'){
						$v['label'] = $v[$displayField];
						unset($v[$displayField]);
					}
					if(isset($v['id'])){
						$v['value'] = $v['id'];
						unset($v['id']);
					}
				}
			}
		}
		if(!empty($e['post'])){
			if(!isset($e['post']['type']))
				$e['post']['type'] = 'text';
		}
// print_r($e);			
		return $e;
	}
	
	function display($colsInRow = 1){
// $this->tool->p_t("Before display");
		$hidden = array("<table id='hidden_elements'><tr>");
		$normal = array("<table id='normal_elements' class='ces' style='width:100%'>");
		$currentCol = 0;
		$evenRow = true;
		$display_status = $this->display_status;
		if($display_status != DISPLAY_STATUS_VIEW)
			$display_status = DISPLAY_STATUS_EDIT;
// print_r($this->elements);		
		foreach($this->elements as $k=>$cell){
			if($cell['type'] == 'hidden'){
				$hidden[] = "<td><input type='hidden' name='{$cell['name']}' id='{$cell['id']}' value='{$cell['value']}'></td>";
			}
			else{
				if($currentCol ++ == 0){
					$evenRowClass = $evenRow ? 'evenRow' : 'oddRow';
					$normal[] = "<tr id='ces_tr_{$cell['id']}' 'class='ces $evenRowClass'>";
					$evenRow = !$evenRow;
				}
				
				$e = cellFactory::get($cell, $this->value);//new kf_cell($cell);
				$params = $e->getParams();
				$normal[] = "<td id='td_label_{$params['id']}' class='pre-td'>";
				$normal[] = $e->pre($display_status);
				$normal[] = "</td>";
				$width = '';
				$hasPost = false;
				if(!empty($params['post']) && ($display_status == DISPLAY_STATUS_EDIT || $params['post']['type'] == 'text')){
					$hasPost = true;
				}
				if($hasPost){
					$normal[] = "<td><table style='width:100%'><tr style='width:100%'>";
					$width = "style='width:100%;'";
				}
				$normal[] = "<td id='td_{$params['id']}' class='cont-td' $width>";
				$normal[] = $e->display($display_status);
				$normal[] = "</td>";
				// display the post
				if($hasPost){
					$normal[] = "<td id='post_{$params['id']}' class='post-td' style='width:auto' style='white-space: nowrap' nowrap='nowrap'>";
					$normal[] = $e->post($display_status);
					$normal[] = "</td>";
					$normal[] = "</tr></table></td>";
				}

				if($currentCol == $colsInRow){
					$normal[] = "</tr>";
					$currentCol = 0;
				}
			}
		}
		if($currentCol != 0)
			$normal[] = "</tr>";
		$hidden[] = "</tr></table>";
		$normal[] = '</table>';
// print_r($hidden)		;
// print_r($normal);
// $this->tool->p_t("After display");

		return implode("\n", $hidden)."\n".implode("\n", $normal);
	}
}
?>