<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//批次管理
class qygl_pici extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'批次', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'合作伙伴'),
			'gx_id'=>array('label'=>'工序'),
			'wz_id'=>array('label'=>'物资'),
			'amount'=>array('label'=>'原始总数量'),
			'remained'=>array('label'=>'当前剩余量'),
			'detail'=>array('label'=>'详细信息', 'formatter'=>'multi_row_edit','legend'=>'详细信息', 'data_source_db'=>'qygl', 'data_source_table'=>'pici_detail',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"有[%(defect_id)s]的初始数量为%(amount)s，目前剩余量%(remained)s，单价%(price)s"),
			),
			'happen_date'=>array('label'=>'生成日期'),
			'created'=>array('label'=>'记录日期', 'editable'=>false),
        );
	}
	
	protected function getButtons(){
        $buttons = parent::getButtons();
		unset($buttons['add']);
		return $buttons;
	}
}
