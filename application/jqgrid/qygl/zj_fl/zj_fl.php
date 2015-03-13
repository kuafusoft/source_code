<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//资金类型管理
class qygl_zj_fl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'资金类型', 'editrules'=>array('required'=>true)),
        );
	}
}
