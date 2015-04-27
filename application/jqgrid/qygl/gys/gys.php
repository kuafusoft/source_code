<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//供应商管理
class qygl_gys extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'gender_id'=>array('label'=>'性别'),
			'zhengjian_fl_id'=>array('label'=>'证件类型', 'hidden'=>true),
            'identity_no'=>array('label'=>'证件号码', 'hidden'=>true),
            'credit_level_id'=>array('label'=>'信用级别', 'hidden'=>true),
            'bank_account_no'=>array('label'=>'银行账号', 'hidden'=>true),
			'init_date'=>array('label'=>'建档日期'),
			'init_account_receivable'=>array('label'=>'初期应收金额'),
            'account_receivable'=>array('label'=>'应收金额'),
            'address'=>array('label'=>'地址', 'hidden'=>true),
			'gys_contact_method'=>array('label'=>'联系方式', 'formatter'=>'multi_row_edit', 'legend'=>'联系方式', 'data_source_table'=>'gys_contact_method',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(contact_method_id)s:%(content)s'), 'from'=>'qygl.gys_contact_method'
			),
			'gys_wz_id'=>array('label'=>'可提供', 'editable'=>true, 'data_source_table'=>'wz'),
            'isactive',
        );
		$this->linkTables = array(
			'm2m'=>array(
				'gys_wz'=>array('link_table'=>'gys_wz', 'self_link_field'=>'gys_id', 'link_field'=>'wz_id', 'refer_table'=>'wz'),
				),
			'one2m'=>array(
				'gys_contact_method'=>array('link_table'=>'gys_contact_method', 'self_link_field'=>'gys_id'),
				),
			);
		
		$this->options['edit'] = array('name', 'gender_id', 'zhengjian_fl_id', 'identity_no', 
			'credit_level_id', 'bank_account_no', 'init_date', 'init_account_receivable', 'account_receivable', 'address',
			'gys_contact_method', 'gys_wz_id',
		);
		// $this->options['navOptions']['refresh'] = false;
	}
	
	protected function _setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'gys_id', 'db'=>'qygl', 'table'=>'yw_cg');
	}
	
	protected function contextMenu(){
		$menu = array(
			'cg'=>'采购',
			'xs'=>'接订单',
			'scdj'=>'生产登记',
		);
		return $menu;
	}
	
    protected function getButtons(){
        $buttons = array(
			'ask2review'=>array('caption'=>'申请审核'),
			'sign'=>array('caption'=>'正式签署'),
			'change'=>array('caption'=>'统一调整'), //统一调整工种、职位、基本工资、提成比例等
            // 'scdj'=>array('caption'=>'生产登记'),
			'jsgz'=>array('caption'=>'生成工资单'),
            // 'gz'=>array('caption'=>'发工资'),
        );
        return array_merge($buttons, parent::getButtons());
    }
}
