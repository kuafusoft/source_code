<?php
require_once('table_desc.php');

class workflow_prj_trace extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'daily_note_id',
			'prj_id',
			'progress'
		);
		$this->options['edit'] = array('prj_id', 'progress');
    } 
}
