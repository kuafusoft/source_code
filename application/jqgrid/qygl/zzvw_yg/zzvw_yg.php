<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//员工管理
class qygl_zzvw_yg extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'hb';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'gender_id'=>array('label'=>'性别'),
			'zhengjian_fl_id'=>array('label'=>'证件类型', 'hidden'=>true),
            'identity_no'=>array('label'=>'证件号码', 'hidden'=>true),
            'credit_level_id'=>array('label'=>'信用级别', 'hidden'=>true),
            'bank_account_no'=>array('label'=>'银行账号', 'hidden'=>true),
			'init_date'=>array('label'=>'建档日期'),
			'init_account_receivable'=>array('label'=>'初期应收金额', 'post'=>array('value'=>'元')),
            'account_receivable'=>array('label'=>'当前应收金额', 'post'=>array('value'=>'元')),
            'address'=>array('label'=>'地址', 'hidden'=>true),
			'hb_contact_method'=>array('label'=>'联系方式', 'formatter'=>'multi_row_edit', 'legend'=>'',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(contact_method_id)s:%(content)s')
			),
			'enter_date'=>array('label'=>'进厂日期', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'work_type_id'=>array('label'=>'工种', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'dept_id'=>array('label'=>'部门', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'position_id'=>array('label'=>'职位', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'salary_fl_id'=>array('label'=>'工资类型', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'base_salary'=>array('label'=>'基本工资', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'ticheng_ratio'=>array('label'=>'提成比例', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'baoxian_type_id'=>array('label'=>'保险类型', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'baoxian_start_date'=>array('label'=>'保险起付日期', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			'baoxian_feiyong'=>array('label'=>'保险费用', 'editable'=>true, 'from'=>'qygl.hb_yg'),
			
			'hb_skill'=>array('label'=>'员工技能', 'formatter'=>'multi_row_edit', 'legend'=>'',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(skill_id)s:%(skill_grade_id)s')
			),
			'hb_hobby_id'=>array('label'=>'爱好', 'editable'=>true, 'data_source_table'=>'hobby'),
			'lxr'=>array('label'=>'紧急联系人'),
			'cell_no'=>array('label'=>'联系人手机'),
            'isactive'=>array('label'=>'状态'),
        );
		
		$this->options['edit'] = array('name', 'gender_id', 'zhengjian_fl_id', 'identity_no', 
			'credit_level_id', 'bank_account_no', 'init_date', 'init_account_receivable', 'account_receivable', 'address',
			'hb_contact_method', 'enter_date', 'work_type_id', 'dept_id', 'position_id', 'salary_fl_id', 'base_salary', 'ticheng_ratio', 'hb_skill',
			'baoxian_type_id', 'baoxian_start_date', 'baoxian_feiyong', 'hb_hobby_id', 'lxr', 'cell_no'
		);
		$this->linkTables = array(
			'm2m'=>array(
				'hb_hobby'=>array('link_table'=>'hb_hobby', 'self_link_field'=>'hb_id', 'link_field'=>'hobby_id', 'refer_table'=>'hobby'),
				),
			'one2m'=>array(
				'hb_contact_method'=>array('link_table'=>'hb_contact_method', 'self_link_field'=>'hb_id'),
				'hb_skill'=>array('link_table'=>'hb_skill', 'self_link_field'=>'hb_id'),
				),
			'one2one'=>array(
				'hb_yg'=>array('link_table'=>'hb_yg', 'self_link_field'=>'hb_id')
				)
			);
		// $this->options['navOptions']['refresh'] = false;
	}
	
	// protected function _getLimit($params){
// // print_r($params);		
		// $limit = array();
		// $sql = '';
		// switch($params['table']){
			// case 'yw_cg': //采购，应只返回供应商列表，还不应包括承运人和装卸人
				// $sql = "select DISTINCT hb.id from hb left join hb_hb_fl on hb.id=hb_hb_fl.hb_id LEFT JOIN gys_wz on gys_wz.hb_id=hb.id ".
					// " WHERE hb_hb_fl.hb_fl_id=".HB_FL_GYS." AND gys_wz.wz_id NOT IN (".WZ_YUNSHU.",".WZ_ZHUANGXIE.") ORDER BY id ASC";
				// break; 
			// case 'yw_xs': //销售，应只返回客户列表
				// $sql = "select distinct hb.id from hb left join hb_hb_fl on hb.id=hb_hb_fl.hb_id WHERE hb_hb_fl.hb_fl_id=".HB_FL_KH." ORDER BY id ASC";
				// break;
			// case 'yw_ruku': //入库，应只返回承运人和装卸人
			// case 'yw_chuku'://出库
				// $sql = "select DISTINCT hb.id from hb left join hb_hb_fl on hb.id=hb_hb_fl.hb_id LEFT JOIN gys_wz on gys_wz.hb_id=hb.id ".
					// " WHERE hb_hb_fl.hb_fl_id=".HB_FL_GYS." AND gys_wz.wz_id IN (".WZ_YUNSHU.",".WZ_ZHUANGXIE.") ORDER BY id ASC";
				// break; 
		// }
		// if(!empty($sql)){
// // print_r($sql);			
			// $res = $this->db->query($sql);
			// while($row = $res->fetch())
				// $limit[] = $row['id'];
		// }
		// else
			// $limit = false;
		// // print_r($params);
		// // print_r($limit);
		// return $limit;
		
	// }
	
	// protected function handleFillOptionCondition(){
		// $this->fillOptionConditions['gys_wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'in', 'value'=>array(WZ_FL_YUANLIAO, WZ_FL_SHEBEI, WZ_FL_LAOBAO, WZ_FL_FUWU, WZ_FL_BANGONG, WZ_FL_NENGYUAN, WZ_FL_WEIXIU, WZ_FL_QITA)));
		// $this->fillOptionConditions['kh_wz_id'] = array(array('field'=>'wz_fl_id', 'op'=>'in', 'value'=>array(WZ_FL_CHANPIN)));
	// }
	
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
