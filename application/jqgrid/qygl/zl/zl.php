<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//质量等级管理
class qygl_zl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'质量等级', 'editrules'=>array('required'=>true)),
			'note'=>array('label'=>'备注')
        );
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'zl_id', 'db'=>'qygl', 'table'=>'defect');
	}
}
