<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//资金变动原因管理
class qygl_zj_cause extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'资金变动原因', 'editrules'=>array('required'=>true)),
        );
	}
}
