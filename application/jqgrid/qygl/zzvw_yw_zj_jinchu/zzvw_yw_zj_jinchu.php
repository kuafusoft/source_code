<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//资金管理

class qygl_zzvw_yw_zj_jinchu extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'yw';
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
		$this->options['list'] = array(
			'zj_cause_id'=>array('label'=>'变动原因', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true),
			'zj_fl_id'=>array('label'=>'资金类型', 'editable'=>true),
			'hb_id'=>array('label'=>'客户'),
			'out_zjzh_id'=>array('label'=>'支付账户', 'data_source_table'=>'zjzh', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true),
			'out_zj_pj_id'=>array('label'=>'支付票据', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true, 'data_source_table'=>'zj_pj'),
			'in_zjzh_id'=>array('label'=>'回款账户', 'data_source_table'=>'zjzh', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true),
			'zj_pj'=>array('label'=>'拆分成的票据', 'formatter'=>'multi_row_edit',  'editable'=>true, 'legend'=>'', 
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"%(code)s, 金额%(total_money)s, 到期日 %(expire_date)s")
			),
			'amount'=>array('label'=>'总金额', 'post'=>'元', 'DATA_TYPE'=>'float', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true),
			'cost'=>array('label'=>'费用', 'post'=>'元', 'DATA_TYPE'=>'float', 'from'=>'qygl.yw_zj_jinchu', 'editable'=>true),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'经办人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_yg'),
			'happen_date'=>array('label'=>'办理日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
			
		);
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>4), 'zj_cause_id', 'zj_fl_id', 'hb_id', 
			'out_zjzh_id', 'out_zj_pj_id', 'pj_amount'=>array('label'=>'票据面额', 'disabled'=>false, 'editable'=>true, 'DATA_TYPE'=>'float'), 
			'in_zjzh_id', 'zj_pj',  'cash_zjzh_id'=>array('label'=>'现金账户', 'editable'=>true, 'data_source_table'=>'zjzh'), 'amount', 'cost', 'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2one'=>array(
				array('table'=>'yw_zj_jinchu', 'self_link_field'=>'yw_id')
			),
			'one2m'=>array(
				array('table'=>'zj_pj', 'self_link_field'=>'id', 'link_field'=>'from_yw_id')
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
