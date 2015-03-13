<?php
require_once('table_desc.php');
//员工管理
class qygl_zzvw_hb_yg extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'hb_gender_id'=>array('label'=>'性别'),
			'zhengjian_fl_id'=>array('label'=>'证件类型', 'hidden'=>true),
            'identity_no'=>array('label'=>'证件号码', 'hidden'=>true),
            'bank_account_no'=>array('label'=>'银行账号', 'hidden'=>true),
            'mobile_no'=>array('label'=>'手机'),
            'tele_no'=>array('label'=>'电话', 'hidden'=>true),
			'email'=>array('label'=>'电子邮件', 'hidden'=>true),
			'qq'=>array('label'=>'QQ', 'hidden'=>true),
            'other_contact_method'=>array('label'=>'其他联系方式', 'hidden'=>true),
            'address'=>array('label'=>'地址', 'hidden'=>true),
            'account_receivable'=>array('label'=>'应收金额'),
            'hb_credit_level_id'=>array('label'=>'信用级别', 'hidden'=>true),
			'hb_salary_fl_id'=>array('label'=>'工资类别'),
            'base_salary'=>array('label'=>'基本工资', 'hidden'=>true),
            'ticheng_ratio'=>array('label'=>'提成比例', 'hidden'=>true),
            'work_type_id'=>array('label'=>'工种', 'hidden'=>true),
            'hb_dept_id'=>array('label'=>'部门'),
			'position_id'=>array('label'=>'职位'),
			'ht_state_id'=>array('label'=>'状态'),
			'jbr_id'=>array('label'=>'经办人', 'hidden'=>true),
			'happen_date'=>array('label'=>'签署日期', 'hidden'=>true),
			'note'=>array('label'=>'备注'),
            'isactive',
			'ht_id'=>array('hidden'=>true, 'hidedlg'=>true, 'type'=>'hidden'),
			'ht_ld_id'=>array('hidden'=>true, 'hidedlg'=>true, 'type'=>'hidden'),
        );

		// $this->options['navOptions']['refresh'] = false;
	}
	
	// public function accessMatrix(){
		// $access_matrix = array(
			// 'all'=>array('index'=>true, 'list'=>true, 'export'=>true),
			// 'admin'=>array('all'=>true, ),
		// );
		
		// $access_matrix['row_owner'] = $access_matrix['assistant_admin'] = $access_matrix['admin'];
		
		// return $access_matrix;
	// }
	
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
