<?php
require_once('table_desc.php');

class useradmin_groups extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
            'id'=>array('editable'=>false),
			'name'=>array('unique'=>true),
			'description',
			'users_id'=>array('label'=>'User', 'search'=>true, 'editable'=>true, 'editrules'=>array('required'=>true)),
			'testcase_type_id'=>array('label'=>'Testcase Type', 'editable'=>true, 'search'=>true, 'data_source_db'=>'xt')
        );
		$this->options['edit'] = array('name', 'description', 'users_id', 'testcase_type_id'); 
		$this->linkTables = array('m2m'=>array('users', array('db'=>'xt', 'table'=>'testcase_type', 'link_db'=>'xt', 'link_table'=>'group_testcase_type', 'self_link_field'=>'group_id')));
	}
}
?>