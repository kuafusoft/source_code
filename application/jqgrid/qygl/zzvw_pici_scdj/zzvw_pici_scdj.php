<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//生产登记批次管理
class qygl_zzvw_pici_scdj extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'pici';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'概述', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'员工', 'editable'=>true, 'editrules'=>array('required'=>true), 'data_source_table'=>'zzvw_yg'),
			'gx_id'=>array('label'=>'工序'),
			'wz_id'=>array('label'=>'物资', 'data_source_table'=>'zzvw_wz_cp'),
			'defect_id'=>array('label'=>'缺陷'),
			'price'=>array('label'=>'单价', 'post'=>'元'),
			'amount'=>array('label'=>'数量', 'post'=>array('value'=>'?')),
			'ck_weizhi_id'=>array('label'=>'存放位置'),
			'remained'=>array('label'=>'当前剩余量'),
			'happen_date'=>array('label'=>'生产日期', 'hidden'=>true),
			'created'=>array('label'=>'记录日期', 'editable'=>false),
        );
		
		$this->options['edit'] = array(
			'gx_id', 'wz_id', 'defect_id', 'price', 'amount', 'ck_weizhi_id'
		);
	}
	
	protected function getButtons(){
        $buttons = parent::getButtons();
		unset($buttons['add']);
		return $buttons;
	}
}
