<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//资金票据管理
class qygl_zj_pj extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'zj_fl_id'=>array('label'=>'票据类型', 'data_source_table'=>'zzvw_zj_fl_pj'),
			'code'=>array('label'=>'编号'),
			'total_money'=>array('label'=>'总金额'),
			'expire_date'=>array('label'=>'到期日期'),
			'from_yw_id'=>array('label'=>'来源于', 'data_source_table'=>'yw'),
			'to_yw_id'=>array('label'=>'使用于', 'data_source_table'=>'yw'),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注')
        );
		
		$this->options['add'] = array(
			'zj_fl_id', 'code', 'total_money', 'expire_date', 'dj_id', 'note'
		);
	}
	
	
	
}
