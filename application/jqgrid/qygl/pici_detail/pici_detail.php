<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//批次管理
class qygl_pici_detail extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'pici_id'=>array('label'=>'批次', 'editrules'=>array('required'=>true), 'formatter'=>'int'),
			'defect_id'=>array('label'=>'缺陷'),
			'price'=>array('label'=>'单价'),
			'amount'=>array('label'=>'原始总数量'),
			'remained'=>array('label'=>'当前剩余量'),
			'ck_weizhi_id'=>array('label'=>'仓位'),
			'note'=>array('label'=>'备注'),
        );
	}
}
