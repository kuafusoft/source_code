<?php
require_once('const_def.php');
require_once('toolfactory.php');
require_once("tool_cell.php");
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
			$this->elements[$k] = $this->tool->model2e2($cell, $this->value, $this->display_status);
		}
	}
	
	function display($colsInRow = 1, $colWidth = array()){
// $this->tool->p_t("Before display");
		$hidden = array("<table id='hidden_elements'><tr>");
		$normal = array("<table id='normal_elements' class='ces' style='width:100%'>");
		$normal[] = "<tr>";
		$w1 = 25 / $colsInRow;
		$w2 = 75 / $colsInRow;
		if($colsInRow == 1){
			$w1 = 10;
			$w2 = 90;
		}
		for($i = 0; $i < $colsInRow; $i ++){
			$normal[] = "<th class='ces' style='width:$w1%' /><th class='ces' style='width:$w2%' />";
		}
		$normal[] = "</tr>";
		
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
					$rowClass = $evenRow ? 'evenRow' : 'oddRow';
					$normal[] = "<tr id='ces_tr_{$cell['id']}' class='ces $rowClass'>";
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
				$postClass = '';
				if($hasPost){
					$normal[] = "<td><table style='width:100%'><tr style='width:100%'>";
					$width = "style='width:100%;'";
					$postClass = 'post-td';
				}
				$normal[] = "<td id='td_{$params['id']}' class='$postClass cont-td' $width>";
				$normal[] = $e->display($display_status);
				$normal[] = "</td>";
				// display the post
				if($hasPost){
					$normal[] = "<td id='post_{$params['id']}' class='$postClass' style='width:auto' style='white-space: nowrap' nowrap='nowrap'>";
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