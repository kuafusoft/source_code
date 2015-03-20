<?php
require_once('table_desc.php');

class workflow_work_report extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		//获取最近的10个period
		$period_list = $this->tool->getWeekList(10, 1);
// print_r($period_list);		
		$this->options['list'] = array(
			'period'=>array('edittype'=>'select', 'editoptions'=>array('value'=>$period_list), 'addoptions'=>array('value'=>$period_list)),
			// '*'
		);
// print_r($period_list);		
		$this->options['edit'] = array('period');
		$this->options['add'] = array('period');
// print_r($this->options['add']);		
	}
	
	// protected function getButtons(){
		// $buttons = array();
		// $buttons['import_note'] = array('caption'=>'Import Note', 'buttonimg'=>'', 'title'=>'Import Content From Daily Note');
		// $buttons = array_merge($buttons, parent::getButtons());
		// unset($buttons['subscribe']);
		// unset($buttons['tag']);
	
		// return $buttons;
	// }
	
	// public function paramsForViewEdit($view_params){
		// $ret = parent::paramsForViewEdit($view_params);
		// $ret['cols'] =  2;
		// return $ret;
	// }
}
