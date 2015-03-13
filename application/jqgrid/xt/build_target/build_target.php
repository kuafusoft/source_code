<?php

require_once('table_desc.php');

class xt_build_target extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name'=>array('editable'=>true, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'description'=>array('editable'=>true),
			'os_ids'=>array('editable'=>true),
			'isactive'
		);
		$this->options['gridOptions']['label'] = 'Build Target';
		$this->linkTables = array('os');
    } 
}
