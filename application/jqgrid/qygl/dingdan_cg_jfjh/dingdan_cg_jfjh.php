<?php
require_once('table_desc.php');
//采购订单交付计划及执行情况记录
class qygl_dingdan_cg_jfjh extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'dingdan_cg_id'=>array('label'=>'业务', 'hidden'=>true, 'hidedlg'=>true),
			'plan_date'=>array('label'=>'计划日期'),
			'plan_amount'=>array('label'=>'计划数量'),
			'happen_date'=>array('label'=>'实际日期'),
            'happen_amount'=>array('label'=>'实际数量'),
			'cg_pici_id'=>array('label'=>'批次'),
            'note'=>array('label'=>'备注'),
        );
		$this->options['add'] = array('dingdan_cg_id'=>array('type'=>'hidden'), 'plan_date', 'plan_amount');
		$this->options['edit'] = array('dingdan_cg_id'=>array('type'=>'hidden'), 'plan_date', 'plan_amount', 'happen_date', 'happen_amount', 'note');
		$this->options['parent'] = array('table'=>'dingdan_cg', 'field'=>'dingdan_cg_id');
		$this->options['displayField'] = 'plan_date';
	}
	
	protected function setSubGrid(){ //显示相关的批次
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'dingdan_cg_id', 'db'=>'qygl', 'table'=>'zzvw_cg_pici');
	}
}
