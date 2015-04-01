<?php
require_once('table_desc.php');

class workflow_work_report extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		//获取最近的10个period
		$period_list = $this->tool->getWeekList(10, 1);
		//获取project list用来检索
		$soptions = array(0=>' ');
		$res = $this->db->query("SELECT * FROM prj");
		while($row = $res->fetch()){
			$soptions[$row['id']] = $row['name'];
		}
// print_r($period_list);		
		$this->options['list'] = array(
			'id',
			'period'=>array('edittype'=>'select', 'editoptions'=>array('value'=>$period_list), 'addoptions'=>array('value'=>$period_list)),
			'creater_id'=>array('label'=>'Creater'),
			'work_report_detail'=>array('label'=>'Items', 'editable'=>true, 'data_source_db'=>'workflow', 
				'search'=>true, 'stype'=>'select', 'searchoptions'=>array('value'=>$this->tool->array2str($soptions)), 
				'data_source_table'=>'work_report_detail', 'from'=>'workflow.work_report_detail',
				'formatter'=>'multi_row_edit', 'formatoptions'=>array('subformat'=>'temp', 'temp'=>'[<span class="prop_color_%(item_prop_id)s">%(item_prop_id)s</span>]:[%(prj_id)s]: %(content)s'), 
				'legend'=>'Items'),
			'created'
			// '*'
		);
// print_r($period_list);		
		$this->options['edit'] = array('period'=>array('editable'=>false), 'work_report_detail');
		$this->options['add'] = array('period', 'work_report_detail');
		$this->options['displayField'] = 'period';
		
		$this->linkTables = array(
			'one2m'=>array(
				'work_report_detail'=>array('link_table'=>'work_report_detail', 'self_link_field'=>'work_report_id'),
			)
		);
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
