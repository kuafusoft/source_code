<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//资金管理

class qygl_zzvw_yw_zj_pj_tiexi extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'yw';
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
		$res = $this->tool->query("SELECT * FROM zj_pj WHERE to_yw_id=0");
		$notUsedPJ = $res->fetchAll();
		$this->options['list'] = array(
			'hb_id'=>array('label'=>'贴息方'),
			'zjzh_id'=>array('label'=>'票据账户', 'data_source_table'=>'zzvw_zjzh_pj', 'from'=>'qygl.yw_zj_pj_tiexi', 'editable'=>true),
			'zj_pj_id'=>array('label'=>'票据', 'from'=>'qygl.yw_zj_pj_tiexi', 'editable'=>true, 'addoptions'=>array('value'=>$notUsedPJ)),
			'total_money'=>array('label'=>'票据面额', 'post'=>array('value'=>'元'), 'disabled'=>true),
			'zj_pj'=>array('label'=>'拆分票据', 'formatter'=>'multi_row_edit', 'legend'=>'', 'from'=>'qygl.zj_pj',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'编号:%(code)s, 金额:%(total_money)s元')),
			'cash_zjzh_id'=>array('label'=>'现金账户', 'data_source_table'=>'zzvw_zjzh_cash', 'from'=>'qygl.yw_zj_pj_tiexi'),
			'amount'=>array('label'=>'总金额', 'post'=>array('value'=>'元'), 'from'=>'qygl.yw_zj_pj_tiexi', 'editable'=>true),
			'cost'=>array('label'=>'费用', 'post'=>array('value'=>'元'), 'defval'=>0, 'from'=>'qygl.yw_zj_pj_tiexi', 'editable'=>true),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'经办人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_yg'),
			'happen_date'=>array('label'=>'办理日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
			
		);
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_PJTX), 
			'hb_id', 'zjzh_id', 'zj_pj_id', 'total_money', 'cash_zjzh_id', 'amount', 'zj_pj', 'cost',
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2one'=>array(
				array('table'=>'yw_zj_pj_tiexi', 'self_link_field'=>'yw_id'),
			),
			'one2m'=>array(
				array('table'=>'zj_pj', 'self_link_field'=>'from_yw_id')
			)
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
