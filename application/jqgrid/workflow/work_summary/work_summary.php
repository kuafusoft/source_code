<?php
require_once('table_desc.php');

class workflow_work_summary extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['edit'] = array('period_id', 'name');
	}
	
	protected function getButtons(){
		$buttons = array();
		$buttons['import_note'] = array('caption'=>'Import Note', 'buttonimg'=>'', 'title'=>'Import Content From Daily Note');
		$buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['subscribe']);
		unset($buttons['tag']);
	
		return $buttons;
	}
	
	public function paramsForViewEdit($view_params){
		$ret = parent::paramsForViewEdit($view_params);
		$ret['cols'] =  2;
		return $ret;
	}
}
