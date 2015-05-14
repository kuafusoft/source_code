<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//收货批次管理
class qygl_zzvw_pici_sh extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'pici';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'概述', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'供应商', 'editable'=>true, 'editrules'=>array('required'=>true), 'data_source_table'=>'zzvw_stgys'),
			'dingdan_id'=>array('label'=>'物资订单', 'data_source_table'=>'dingdan', 'hidden'=>true),
			'wz_id'=>array('label'=>'物资'),
			
			'defect_id'=>array('label'=>'缺陷'),
			'amount'=>array('label'=>'原始总数量', 'post'=>array('value'=>'?')),
			'ck_weizhi_id'=>array('label'=>'存放位置'),
			'remained'=>array('label'=>'当前剩余量'),
			'happen_date'=>array('label'=>'生成日期', 'hidden'=>true),
			'created'=>array('label'=>'记录日期', 'editable'=>false),
        );
		
		$this->options['edit'] = array(
			'hb_id', 'dingdan_id', 'wz_id'=>array('disabled'=>true), 'defect_id', 'amount'=>array('label'=>'数量', 'title'=>'输入负数为退货'), 'ck_weizhi_id'
		);
	}
	
	protected function getButtons(){
        $buttons = parent::getButtons();
		unset($buttons['add']);
		return $buttons;
	}
}
