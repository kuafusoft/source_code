<?php
require_once('kf_cell.php');

class kf_label extends kf_cell{
	protected function oneView($v, $props){
		$class = $this->params['class'];
		$strClass = implode(' ', $class);
		$ret = '';
		if(is_array($v)){
			$title = '';
			if(!isset($v['title']) && !empty($v['note']))
				$title = "title='".htmlentities($v['note'])."'";
			
			$label = isset($v['label']) ? $v['label'] : (isset($v['value']) ? $v['value'] : (isset($v['id']) ? $v['id'] : '[unknown]'));
			$ret = "<label class='$strClass' $title>$label</label>";
		}
		else{
			$v = $this->tool->insertLink($v);
			$ret = "<label class='$strClass'>$v</label>";
		}
		return $ret;
	}
	
	protected function oneEdit($v, $props){
		return $this->oneView($v, $props);
	}
}
?>