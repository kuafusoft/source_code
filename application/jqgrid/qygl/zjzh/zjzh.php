<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//资金账户管理
class qygl_zjzh extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'账户', 'editrules'=>array('required'=>true)),
			'account_no'=>array('label'=>'账号'),
			'bizhong_id'=>array('label'=>'币种'),
			'remained'=>array('label'=>'账户余额', 'post'=>'元'),
			'zj_fl_id'=>array('label'=>'资金类型'),
			'owner_id'=>array('label'=>'管理员', 'data_source_table'=>'zzvw_yg', 'data_source_db'=>'qygl'),
			'created'=>array('label'=>'创建时间')
        );
	}
}
