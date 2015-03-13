<?php
require_once('tree_table_desc.php');

class workflow_prj extends tree_table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			// 'pid'=>array('label'=>'Parent Project', 'hidden'=>true, 'hidedlg'=>true, 'editable'=>false, 'edittype'=>'select', 'formatter'=>'select', 'data_source_table'=>'prj'),
			// 'node_id'=>array('hidden'=>true, 'hidedlg'=>true, 'editable'=>false, 'formatter'=>'int'),
			'prj_type_id'=>array('label'=>'Project Type'),
			'name', 
			'description',
			'segment_id',
			'part_id',
			'tool_id',
			'begin_from'=>array('label'=>'Begin From'),
			'prj_phase_id'=>array('label'=>'Current Phase'),
			'progress'=>array('hidden'=>true),
		
			// 'prj_prj_property'=>array('label'=>'Detail', 'formatter'=>'multi_row_edit', 
				// 'formatoptions'=>array('subformat'=>'nv', 'name_field'=>'prj_property_id', 'value_field'=>'content')),
			// 'prj_type_id'=>array('label'=>'Project Type'),
			// 'begin_from'=>array('label'=>'Begin From', 'hidden'=>true), 
			// 'duration'=>array('label'=>'Duration', 'hidden'=>true),
			// 'unit_id'=>array('label'=>'Time Unit', 'hidden'=>true),
			'manager_id',
			'customer_prj'=>array('label'=>'Customer', 'editable'=>true, 'formatter'=>'multi_row_edit', 'legend'=>'Customer',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'Current phase is [%(customer_phase_id)s], MP date is [%(mp_date)s]')),
			'isactive',
			// '*'=>array('hidden'=>true)
		);
		$this->options['real_table'] = 'prj';
		$this->options['edit'] = array('prj_type_id', 'name', 'description', 'segment_id', 'family_id', 'part_id', 'tool_id', 'begin_from', 'prj_phase_id', 'progress', 'customer_prj', 'manager_id');//, 'prj_type_id');//, 'from', 'duration', 'unit_id', 'progress');

		$this->options['gridOptions']['label'] = 'Project';
		// $this->options['parent'] = array('table'=>'prj', 'field'=>'pid');
		
		$this->linkTables = array(
			'one2m'=>array('customer_prj'=>array()), 
			// 'one2m'=>array('prj_prj_property'=>array()),
			// 'treeview'=>array('prj'=>array('table'=>'prj_node', 'tree_table'=>'prj_tree'))
		);
		// $this->parent_table = 'testcase_module';
		// $this->parent_field = 'testcase_module_id';
		
		// $this->parent_field = 'pid';
    } 
	
	// protected function _setSubGrid(){
        // $this->options['gridOptions']['subGrid'] = true;
		// $this->options['subGrid'] = array('expandField'=>'pid', 'db'=>'workflow', 'table'=>'prj');
	// }
	
	public function fillOptions(&$columnDef, $db, $table){
		if ($columnDef['index'] == 'unit_id'){
			$this->tool->fillOptions($db, $table, $columnDef, false, array(array('field'=>'unit_type_id', 'op'=>'=', 'value'=>UNIT_TYPE_TIME)));
		}
		else
			parent::fillOptions($columnDef, $db, $table);
	}

	protected function getButtons(){
        $buttons = array(
			'complete'=>array('caption'=>'Complete the PRJ'),
			'uncomplete'=>array('caption'=>'unComplete the PRJ'),
			'diff'=>array('caption'=>'Tell the difference'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
		return $buttons;
	}
	
	protected function contextMenu(){
		$menu = array(
			'menu_export'=>'Export',
			'write_note'=>'Write Note',
		);
		return $menu;
	}
}
