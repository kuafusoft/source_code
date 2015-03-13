<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//资金管理

class qygl_zj_jinchu extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'zj_cause_id'=>array('label'=>'变动原因'),
			'in_zjzh_id'=>array('label'=>'回款账户', 'data_source_table'=>'zjzh'),
			'out_zjzh_id'=>array('label'=>'支付账户', 'data_source_table'=>'zjzh'),
			'zj_pj_id'=>array('label'=>'票据'),
			'amount'=>array('label'=>'总金额', 'post'=>'元'),
			'cost'=>array('label'=>'费用', 'post'=>'元')
		);
		switch($this->params['yw_fl_id']){
			case YW_FL_ZJIN:
				$this->options['edit'] = array('in_zjzh_id', 
					'pj_code'=>array('label'=>'票据编号', 'DATA_TYPE'=>'varchar', 'LENGTH'=>20, 'IDENTITY'=>FALSE), 
					'expire_date'=>array('label'=>'到期日', 'DATA_TYPE'=>'date', 'LENGTH'=>20, 'IDENTITY'=>FALSE), 
					'amount', 'zj_cause_id', 'cost');
				break;
			case YW_FL_ZJOUT:
				$this->options['edit'] = array('out_zjzh_id', 'zj_pj_id', 'amount', 'zj_cause_id', 'cost');
				break;
			case YW_FL_ZHUANZHANG:
			default:
				$this->options['edit'] = array('in_zjzh_id', 'out_zjzh_id', 'zj_pj_id', 'amount', 'zj_cause_id', 'cost');
				break;
		}
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$hb_tool = new hb_tool($this->tool);
		if($columnDef['name'] == 'out_zjzh_id' || $columnDef['name'] == 'in_zjzh_id'){
			$o = array(0=>'');
			$res = $this->tool->query("SELECT * FROM zjzh");
			while($row = $res->fetch()){
				$row['name'] .= " [账户余额{$row['remained']}元]";
				$o[$row['id']] = $row;
			}
			$columnDef['editoptions']['value'] = $o;
		}
		elseif($columnDef['name'] == 'zj_pj_id'){
			$o = array(0=>'');
			$res = $this->tool->query("select * from zj_pj WHERE to_yw_id=0");
			while($row = $res->fetch()){
				$row['name'] = $row['code']." [总金额{$row['total_money']}元]";
				$o[$row['id']] = $row;
			}
			$columnDef['editoptions']['value'] = $o;
		}
		else{
			parent::fillOptions($columnDef, $db, $table);
		}
	}
}
