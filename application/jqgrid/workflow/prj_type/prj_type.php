<?php
require_once('table_desc.php');

class workflow_prj_type extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$tables = $this->tool->getAllTables('workflow', true);
		$this->options['list'] = array(
			'id', 
			'name', 
			'description',
			'table_name'=>array('label'=>'Table Name', 'edittype'=>'select', 'editoptions'=>array('value'=>$tables)),
			'isactive',
			'prj_type_ids',
		);
		$this->options['edit'] = array('name', 'description', 'table_name', 'prj_type_ids');

		$this->options['gridOptions']['label'] = 'Project Type';
    } 
}
