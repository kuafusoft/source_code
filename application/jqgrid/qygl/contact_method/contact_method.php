<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//技巧等级管理
class qygl_contact_method extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'联系方式', 'editrules'=>array('required'=>true)),
        );
	}
}
