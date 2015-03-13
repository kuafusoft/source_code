<?php
require_once('table_desc.php');

class workflow_prj_type_chip_board extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'prj_id',
			'chip_id',
			'board_type_id'
		);
		$this->options['edit'] = array('chip_id', 'board_type_id');
    } 
}
