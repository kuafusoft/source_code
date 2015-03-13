<?php
require_once('table_desc.php');

class workflow_work_summary_detail extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'content',
			'work_summary_id'=>array('hidden'=>true, 'hidedlg'=>true), //'editable'=>false),
			'daily_note_id'=>array('hidden'=>true, 'editable'=>false),
			'*'
		);
		$this->options['edit'] = array('work_summary_id', 'prj_id', 'content', 'item_prop_id');

		$this->options['gridOptions']['label'] = 'Work Summary Items';
		$this->options['navOptions']['del'] = true;
		
		$this->parent_field = 'work_summary_id';
    } 
	
	protected function getButtons(){
		$buttons = array();
		$buttons['import_note'] = array('caption'=>'Import Note', 'buttonimg'=>'', 'title'=>'Import Content From Daily Note');
		$buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['subscribe']);
		unset($buttons['tag']);
		unset($buttons['export']);
	
		return $buttons;
	}
}
