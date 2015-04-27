<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_fh_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		if(!empty($this->params['zzvw_pici_fh'])){
			foreach($this->params['zzvw_pici_fh']['data'] as &$data){
				$data['happen_date'] = $this->params['happen_date'];
				$data['remained'] = $data['amount'];
			}
		}
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		
		$res = $this->tool->query("SELECT name FROM yw_fl WHERE id={$pair['yw_fl_id']}");
		$yw_fl = $res->fetch();
		$name = $hb['name'].'在'.$pair['happen_date'].$yw_fl['name'];
		$pair['name'] = $name;
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		//更新承运人和装卸人的应收款信息
		//运费
		$yunfei = floatval($this->params['weight']) * floatval($this->params['yunshu_price']);
		$this->tool->query("UPDATE hb set account_receivable=account_receivable-$yunfei WHERE id={$this->params['hb_id']}");
		//装卸费
		if(!empty($this->params['zxr_id'])){
			$zhuangxiefei = floatval($this->params['weight']) * floatval($this->params['zx_price']);
			$this->tool->query("UPDATE hb set account_receivable=account_receivable-$zhuangxiefei WHERE id={$this->params['zxr_id']}");
		}
// print_r("yunfei = $yunfei, zhuangxiefei = $zhuangxiefei\n");		
		return $ret;
	}
	
}

?>