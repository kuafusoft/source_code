<?php
require_once('table_desc.php');

class xt_board_type extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name'=>array('editable'=>true, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'chip_type_ids'=>array('editable'=>true),
			'isactive'
		);
		$this->options['gridOptions']['label'] = 'Board Type';
		$this->linkTables = array('chip_type');
    } 
}
?>