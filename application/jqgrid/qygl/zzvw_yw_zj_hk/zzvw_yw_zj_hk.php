<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//资金管理

class qygl_zzvw_yw_zj_hk extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'yw';
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
		$this->options['list'] = array(
			'zj_cause_id'=>array('label'=>'回款原因', 'data_source_table'=>'zzvw_zj_cause_hk', 'from'=>'qygl.yw_zj_hk', 'editable'=>true, 'editrules'=>array('required'=>true)),
			'zj_fl_id'=>array('label'=>'资金类型', 'editable'=>true, 'from'=>'qygl.yw_zj_hk'),
			'hb_id'=>array('label'=>'付款方'),
			'zjzh_id'=>array('label'=>'进入账户', 'data_source_table'=>'zjzh', 'from'=>'qygl.yw_zj_hk', 'editable'=>true),
			// 'zj_pj_id'=>array('label'=>'票据', 'from'=>'qygl.yw_zj_hk', 'editable'=>true),
			'zj_pj_fl_id'=>array('label'=>'票据类型', 'from'=>'qygl.zj_pj', 'editable'=>true),
			'code'=>array('label'=>'票据编号', 'from'=>'qygl.zj_pj', 'editable'=>true),
			// 'total_money'=>array('label'=>'票据面额', 'from'=>'qygl.zj_pj', 'editable'=>true, 'disabled'=>true),
			'expire_date'=>array('label'=>'到期日', 'DATA_TYPE'=>'date', 'from'=>'qygl.zj_pj', 'editable'=>true),
			'total_money'=>array('label'=>'票据金额', 'post'=>array('value'=>'元'), 'from'=>'qygl.zj_pj', 'editable'=>true, 'hidden'=>true, 'hidedlg'=>true),
			'amount'=>array('label'=>'总金额', 'post'=>array('value'=>'元'), 'from'=>'qygl.yw_zj_hk', 'editable'=>true),
			'cost'=>array('label'=>'费用', 'post'=>array('value'=>'元'), 'defval'=>0, 'from'=>'qygl.yw_zj_hk', 'editable'=>true),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'经办人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_yg'),
			'happen_date'=>array('label'=>'办理日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
			
		);
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_HK), 'zj_cause_id', 'zj_fl_id', 
			'hb_id', 'zjzh_id', 'zj_pj_fl_id', 'code', 'expire_date', 'amount', 'cost',
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2one'=>array(
				array('table'=>'yw_zj_hk', 'self_link_field'=>'yw_id'),
				array('table'=>'zj_pj', 'self_link_field'=>'from_yw_id')
			),
			// 'one2m'=>array(
				// array('table'=>'zj_pj', 'self_link_field'=>'id', 'link_field'=>'from_yw_id')
			// )
		);
	}
	
	// public function fillOptions(&$columnDef, $db, $table){
		// $hb_tool = new hb_tool($this->tool);
		// if($columnDef['name'] == 'out_zjzh_id' || $columnDef['name'] == 'in_zjzh_id'){
			// $o = array(0=>'');
			// $res = $this->tool->query("SELECT * FROM zjzh");
			// while($row = $res->fetch()){
				// $row['name'] .= " [账户余额{$row['remained']}元]";
				// $o[$row['id']] = $row;
			// }
			// $columnDef['editoptions']['value'] = $o;
		// }
		// elseif($columnDef['name'] == 'zj_pj_id'){
			// $o = array(0=>'');
			// $res = $this->tool->query("select * from zj_pj WHERE to_yw_id=0");
			// while($row = $res->fetch()){
				// $row['name'] = $row['code']." [总金额{$row['total_money']}元]";
				// $o[$row['id']] = $row;
			// }
			// $columnDef['editoptions']['value'] = $o;
		// }
		// else{
			// parent::fillOptions($columnDef, $db, $table);
		// }
	// }
}
