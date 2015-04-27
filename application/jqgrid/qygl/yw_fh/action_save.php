<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_yw_fh_action_save extends action_save{
	// protected function fillDefaultValues($action, &$pair, $db, $table){
		// if(!empty($this->params['zzvw_sc_pici'])){
			// foreach($this->params['zzvw_sc_pici']['data'] as &$data){
				// $data['happen_date'] = $this->params['happen_date'];
				// $data['remained'] = $data['amount'];
			// }
		// }
		// parent::fillDefaultValues($action, $pair, $db, $table);
	// }
	
	protected function afterSave($affectID){
		//更新批次信息
		foreach($this->params['zzvw_sc_pici']['data'] as $pici){
			$this->tool->query("UPDATE sc_pici set remained=remained-{$pici['amount']} WHERE id={$pici['sc_pici_id']}");
		}
		// $ret = parent::afterSave($affectID);
		//更新承运人和装卸人的应收款信息
		//运费
		$yunfei = floatval($this->params['weight']) * floatval($this->params['yunshu_price']);
		$this->tool->query("UPDATE gys set account_receivable=account_receivable-$yunfei WHERE id={$this->params['cyr_id']}");
		//装卸费
		$zhuangxiefei = floatval($this->params['weight']) * floatval($this->params['zx_price']);
// print_r("yunfei = $yunfei, zhuangxiefei = $zhuangxiefei\n");		
		$this->tool->query("UPDATE yg set account_receivable=account_receivable-$zhuangxiefei WHERE id={$this->params['zxr_id']}");
		return $ret;
	}

}

?>