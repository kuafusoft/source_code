<?php
require_once('kf_cell.php');
/*
比较复杂的一个组件，主要用于大量选项的情况。在这种情况下，用select不是很合适，用auto_complete更好。
由部分构成：id和name，其中id隐藏
*/
class kf_auto_complete extends kf_cell{
	protected function init($params, $values){
		$params['type'] = 'text';
		// $params['event'] = array('onkeypress'=>"XT.auto_complete(this,\"{$params['db']}\",\"{$params['table']}\");");
		parent::init($params, $values);
	}
	
	public function display($display_status = DISPLAY_STATUS_EDIT){
		$ret = "<input type='hidden' id={$this->params['name']}'>";
		$text_params = $this->params;
		$text_params['id'] = 'auto_complete_'.$this->params['id'];
		$text_params['real_id'] = $this->params['id'];
		$text_params['name'] = 'auto_complete_'.$this->params['name'];
		$text_params['ignored'] = 'ignored';
		$text_params['auto_complete'] = 'auto_complete';
		$text = cellFactory::get($text_params);
		$ret .= $text->display($display_status);
		return $ret;
	}
}
?>