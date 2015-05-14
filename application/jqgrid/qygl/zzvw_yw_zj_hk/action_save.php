<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_zj_hk_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		if($this->params['zj_fl_id'] == ZJ_FL_PIAOJU)
			$this->params['total_money'] = $this->params['amount'];
		$pair['yw_fl_id'] = YW_FL_HK;
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		
		$res = $this->tool->query("SELECT * FROM zj_cause WHERE id={$this->params['zj_cause_id']}");
		$zj_cause = $res->fetch();
		$name = $pair['happen_date'].'收到'.$hb['name'].$zj_cause['name'].'款项'.$this->params['amount'].'元';
		$pair['name'] = $name;
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		
		//更新交易人的应收款信息以及账户余额
		$sql1 = "UPDATE hb set account_receivable=account_receivable-{$this->params['amount']} WHERE id={$this->params['hb_id']}";
		$this->tool->query($sql1);
		//更新账户余额
		$sql2 = "UPDATE zjzh set remained=remained+{$this->params['amount']} WHERE id={$this->params['zjzh_id']}";
		$this->tool->query($sql2);
		return $ret;
	}
	
	protected function saveOne2One($affectedID, $linkInfo){
		if($linkInfo['table'] == 'zj_pj'){
			if($this->params['zj_fl_id'] == ZJ_FL_PIAOJU)
				parent::saveOne2One($affectedID, $linkInfo);
		}
		else
			parent::saveOne2One($affectedID, $linkInfo);
	}
}

?>