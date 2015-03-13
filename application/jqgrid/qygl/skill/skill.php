<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//员工管理
class qygl_skill extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'note'=>array('label'=>'备注')
        );
	}
}
