<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_zzvw_xs_pici_action_save extends action_save{
	// protected function fillDefaultValues($action, &$pair, $db, $table){
		// parent::fillDefaultValues($action, $pair, $db, $table);
// // print_r($pair);		
		// //获取订单信息
		// $res = $this->tool->query("SELECT gys_id, wz_id FROM dingdan_cg left join yw_cg on dingdan_cg.yw_cg_id=yw_cg.id WHERE dingdan_cg.id={$pair['dingdan_cg_id']}");
		// $dingdan = $res->fetch();
		// $pair['name'] = sprintf("%10s-%08d-%08d", $pair['happen_date'], $dingdan['gys_id'], $dingdan['wz_id']);
	// }
	
	//应更新物资情况
	protected function afterSave($affectID){
		// 更新采购执行计划
		$res = $this->tool->query("select * from dingdan_xs_jfjh WHERE dingdan_xs_id={$this->params['dingdan_xs_id']} and happen_amount=0 order by plan_date ASC limit 1");
		if($jfjh = $res->fetch()){ //有未执行的交付计划
			$jfjh['happen_date'] = $this->params['happen_date'];
			$jfjh['happen_amount'] = $this->params['amount'];
			$jfjh['xs_pici_id'] = $affectID;
			$this->tool->update('dingdan_xs_jfjh', $jfjh);
		}
		else{ //没有未执行的交付计划
			$jfjh = array('xs_pici_id'=>$affectID, 'dingdan_xs_id'=>$this->params['dingdan_xs_id'], 'happen_date'=>$this->params['happen_date'], 'happen_amount'=>$this->params['amount']);
			$this->tool->insert('dingdan_xs_jfjh', $jfjh);
		}
		//更新订单完成情况
		$res = $this->tool->query("select * from dingdan_xs WHERE id={$this->params['dingdan_xs_id']} and defect_id={$this->params['defect_id']}");
		if($dingdan_xs = $res->fetch()){
			$dingdan_xs['completed_amount'] = $dingdan_xs['completed_amount'] + $this->params['amount'];
			$this->tool->update('dingdan_xs', $dingdan_xs);
		}
		else{
			
		}
		
		//更新物资数量
		$this->tool->query("update wz set remained=remained-{$this->params['amount']} WHERE id={$dingdan_xs['wz_id']}");
	}
}

?>