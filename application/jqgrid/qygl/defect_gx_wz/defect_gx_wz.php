<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//技巧等级管理
class qygl_defect_gx_wz extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'wz_id'=>array('label'=>'物资'),
			'gx_id'=>array('label'=>'工序'),
            'defect_id'=>array('label'=>'缺陷', 'editrules'=>array('required'=>true)),
        );
	}

	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['gx_id'] = array(array('field'=>'gx_fl_id', 'op'=>'<>', 'value'=>GX_FL_FSCX));
	}
}
