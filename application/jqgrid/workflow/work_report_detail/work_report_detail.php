<?php
require_once('table_desc.php');

class workflow_work_report_detail extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'work_report_id'=>array('hidden'=>true, 'hidedlg'=>true, 'editable'=>false, 'width'=>100),
			'prj_id'=>array('label'=>'Project'),
			'item_prop_id'=>array('label'=>'Property'),
			'content',
			'comment'
		);
		$this->options['edit'] = array('prj_id', 'item_prop_id', 'content');

		// $this->options['gridOptions']['label'] = 'Work Report Items';
		// $this->options['navOptions']['del'] = true;
		
		// $this->parent_field = 'work_report_id';
    } 
	
	// protected function getButtons(){
		// $buttons = array();
		// $buttons['import_note'] = array('caption'=>'Import Note', 'buttonimg'=>'', 'title'=>'Import Content From Daily Note');
		// $buttons = array_merge($buttons, parent::getButtons());
		// unset($buttons['subscribe']);
		// unset($buttons['tag']);
		// unset($buttons['export']);
	
		// return $buttons;
	// }
}
