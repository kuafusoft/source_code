<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_zzvw_pici_sh_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
// print_r($pair);		
		//获取订单信息
		$res = $this->tool->query("SELECT hb.name as hb, wz.name as wz, hb_id, wz_id, unit.name as unit ".
			" FROM dingdan left join yw on dingdan.yw_id=yw.id ".
			"left join wz on dingdan.wz_id=wz.id left join hb on yw.hb_id=hb.id left join unit on wz.unit_id=unit.id".
			" WHERE dingdan.id={$pair['dingdan_id']}");
		$dingdan = $res->fetch();
		$amount = floatval($pair['amount']);
		$action = '收到';
		if($amount < 0){
			$amount = -$amount;
			$action = '退还';
		}
		$pair['name'] = "{$pair['happen_date']}$action{$dingdan['hb']} {$dingdan['wz']} $amount{$dingdan['unit']}";
// print_r($pair);		
	}
	
	//应更新物资情况
	protected function afterSave($affectID){
		// 更新采购执行计划
		$res = $this->tool->query("select * from dingdan_jfjh WHERE dingdan_id={$this->params['dingdan_id']} and happen_amount=0 order by plan_date ASC limit 1");
		if($jfjh = $res->fetch()){ //有未执行的交付计划
			$jfjh['happen_date'] = $this->params['happen_date'];
			$jfjh['happen_amount'] = $this->params['amount'];
			$jfjh['pici_id'] = $affectID;
			$this->tool->update('dingdan_jfjh', $jfjh);
		}
		else{ //没有未执行的交付计划
			$jfjh = array('pici_id'=>$affectID, 'dingdan_id'=>$this->params['dingdan_id'], 'happen_date'=>$this->params['happen_date'], 'happen_amount'=>$this->params['amount']);
			$this->tool->insert('dingdan_jfjh', $jfjh);
		}
		//更新订单完成情况
		$res = $this->tool->query("select * from dingdan WHERE id={$this->params['dingdan_id']} and defect_id={$this->params['defect_id']}");
		if($dingdan = $res->fetch()){
			$dingdan['completed_amount'] = $dingdan['completed_amount'] + $this->params['amount'];
			$this->tool->update('dingdan', $dingdan);
		}
		else{
			
		}
		
		//更新物资数量
		$this->tool->query("update wz set remained=remained+{$this->params['amount']} WHERE id={$dingdan['wz_id']}");
		//更新供应商的应收款信息
		$total_money = $this->params['amount'] * $dingdan['price'];
		$this->tool->query("UPDATE hb set account_receivable=account_receivable - $total_money WHERE id={$this->params['hb_id']}");
	}
}

?>