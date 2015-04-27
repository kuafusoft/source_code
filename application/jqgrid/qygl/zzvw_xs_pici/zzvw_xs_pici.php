<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//批次管理
class qygl_zzvw_xs_pici extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'xs_pici';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'批次', 'editrules'=>array('required'=>true)),
			'kh_id'=>array('label'=>'客户', 'editable'=>true, 'editrules'=>array('required'=>true)),
			'dingdan_xs_id'=>array('label'=>'物资订单'),
			'wz_id'=>array('label'=>'物资'),
			'sc_pici_id'=>array('label'=>'产品批次'),
			
			'amount'=>array('label'=>'原始总数量', 'post'=>array('value'=>'?')),
			'remained'=>array('label'=>'当前剩余量'),
			'happen_date'=>array('label'=>'生成日期'),
			'created'=>array('label'=>'记录日期', 'editable'=>false),
        );
		
		$this->options['edit'] = array(
			'kh_id', 'dingdan_xs_id', 'wz_id'=>array('disabled'=>true), 'sc_pici_id', 'amount'=>array('label'=>'数量', 'title'=>'输入负数为客户退货')
		);
	}
	
	protected function getButtons(){
        $buttons = parent::getButtons();
		unset($buttons['add']);
		return $buttons;
	}
}
