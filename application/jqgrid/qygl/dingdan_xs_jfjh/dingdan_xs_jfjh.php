<?php
require_once('table_desc.php');
//采购订单交付计划及执行情况记录
class qygl_dingdan_xs_jfjh extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'dingdan_xs_id'=>array('label'=>'业务', 'hidden'=>true, 'hidedlg'=>true),
			'plan_date'=>array('label'=>'计划日期'),
			'plan_amount'=>array('label'=>'计划数量'),
			'happen_date'=>array('label'=>'实际日期'),
            'happen_amount'=>array('label'=>'实际数量'),
            'note'=>array('label'=>'备注'),
        );
		$this->options['add'] = array('dingdan_xs_id'=>array('type'=>'hidden'), 'plan_date', 'plan_amount');
		$this->options['edit'] = array('dingdan_xs_id'=>array('type'=>'hidden'), 'plan_date', 'plan_amount', 'happen_date', 'happen_amount', 'note');
		$this->options['parent'] = array('table'=>'dingdan_xs', 'field'=>'dingdan_xs_id');
		$this->options['displayField'] = 'plan_date';
	}
}
