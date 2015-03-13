<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//员工管理
class qygl_hb extends table_desc{
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
            'account_receivable'=>array('label'=>'应收金额'),
            'address'=>array('label'=>'地址', 'hidden'=>true),
			'hb_contact_method'=>array('label'=>'联系方式', 'formatter'=>'multi_row_edit', 'legend'=>'联系方式', 'data_source_table'=>'hb_contact_method',
				'formatoptions'=>array('subformat'=>'nv', 'name_field'=>'contact_method_id', 'value_field'=>'content'), 'from'=>'qygl.hb_contact_method'
			),
			'hb_fl_id'=>array('label'=>'类型', 'search'=>true, 'editable'=>true, 'editrules'=>array('required'=>true)),
			'hb_yg'=>array('label'=>'其他信息', 'from'=>'qygl.hb_yg', 'data_source_table'=>'hb_yg', 'formatter'=>'embed_table'),
			'hb_skill'=>array('label'=>'员工技能', 'from'=>'qygl.hb_skill', 'formatter'=>'multi_row_edit'),
			
			'gys_wz_id'=>array('label'=>'可提供', 'editable'=>true, 'formatter'=>'ids', 'data_source_table'=>'wz'),
			'kh_wz_id'=>array('label'=>'需采购', 'editable'=>true, 'formatter'=>'ids', 'data_source_table'=>'wz'),
			
            'isactive',
        );
		$this->linkTables = array(
			'm2m'=>array(
				'hb_fl'=>array('link_table'=>'hb_hb_fl', 'self_link_field'=>'hb_id'), 
				'gys_wz'=>array('link_table'=>'gys_wz', 'self_link_field'=>'hb_id', 'link_field'=>'wz_id', 'refer_table'=>'wz'),
				'kh_wz'=>array('link_table'=>'kh_wz', 'self_link_field'=>'hb_id', 'link_field'=>'wz_id', 'refer_table'=>'wz'),
				),
			'one2m'=>array(
				'hb_contact_method'=>array('link_table'=>'hb_contact_method', 'self_link_field'=>'hb_id'),
				'hb_skill'=>array('link_table'=>'hb_skill', 'self_link_field'=>'hb_id'),
				),
			'one2one'=>array(
				'hb_yg'=>array('db'=>'qygl', 'table'=>'hb_yg', 'link_table'=>'hb_yg', 'self_link_field'=>'hb_id'),
			
				)
			);
		
		$this->options['edit'] = array('name', 'gender_id', 'zhengjian_fl_id', 'identity_no', 
			'credit_level_id', 'bank_account_no', 'account_receivable', 'address',
			'hb_contact_method', 'hb_fl_id', 
			'hb_yg',
			'hb_skill',
			'gys_wz_id',
			'kh_wz_id'
		);
		// $this->options['navOptions']['refresh'] = false;
	}
	
	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['gys_wz_ids'] = array(array('field'=>'wz_fl_id', 'op'=>'in', 'value'=>array(WZ_FL_YUANLIAO, WZ_FL_SHEBEI, WZ_FL_LAOBAO, WZ_FL_FUWU, WZ_FL_BANGONG, WZ_FL_NENGYUAN, WZ_FL_WEIXIU, WZ_FL_QITA)));
		$this->fillOptionConditions['kh_wz_ids'] = array(array('field'=>'wz_fl_id', 'op'=>'in', 'value'=>array(WZ_FL_CHANPIN)));
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
