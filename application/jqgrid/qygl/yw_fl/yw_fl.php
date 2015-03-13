<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//业务分类管理
class qygl_yw_fl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'description'=>array('label'=>'描述')
        );
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'yw_fl_id', 'db'=>'qygl', 'table'=>'yw');
	}
}
