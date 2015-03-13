<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/wz_tool.php");
//生产情况管理

class qygl_scdj extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id',
			'yw_id'=>array('label'=>'业务'),
			'gx_id'=>array('label'=>'工序', 'editrules'=>array('required'=>true)),
			'wz_id'=>array('label'=>'产品', 'editrules'=>array('required'=>true)),
			'pici_id'=>array('label'=>'批次')
		);
		$this->options['edit'] = array('gx_id', 'wz_id', 'defect_id'=>array('label'=>'缺陷'), 
			'amount'=>array('label'=>'数量'), 
			'price'=>array('label'=>'单价', 'post'=>'元/个', 'DATA_TYPE'=>'float'),
			'ck_weizhi_id'=>array('label'=>'位置')
		);
	}
	
	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['gx_id'] = array(array('field'=>'gx_fl_id', 'op'=>'<>', 'value'=>GX_FL_FSCX));
		$this->fillOptionConditions['wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'=', 'value'=>WZ_FL_CHANPIN));
		
		$this->allFields['wz_id'] = $this->allFields['gx_id'] = true;
	}
}
