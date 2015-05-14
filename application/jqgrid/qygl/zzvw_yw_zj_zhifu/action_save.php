<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_zj_zhifu_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
// print_r($this->params);		
// Array
// (
    // [db] => qygl
    // [table] => zzvw_yw_zj_jinchu
    // [parent] => 0
    // [cloneit] => false
    // [yw_fl_id] => 4
    // [zj_cause_id] => 8
    // [zj_fl_id] => 1
    // [out_zjzh_id] => 
    // [out_zj_pj_id] => 
    // [pj_amount] => 
    // [hb_id] => 2
    // [in_pj_zjzh_id] => 
    // [in_cash_zjzh_id] => 1
    // [amount] => 111
    // [cost] => 0
    // [dj_id] => 
    // [note] => 
    // [jbr_id] => 2
    // [happen_date] => 2015-05-05
    // [id] => 0
    // [real_table] => yw
// )
		parent::fillDefaultValues($action, $pair, $db, $table);
		$pair['yw_fl_id'] = YW_FL_ZHIFU;
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		
		$res = $this->tool->query("SELECT * FROM zj_cause WHERE id={$this->params['zj_cause_id']}");
		$zj_cause = $res->fetch();
		$name = $pair['happen_date'].'因'.$zj_cause['name'].'支付给'.$hb['name'].$this->params['amount'].'元';
		$pair['name'] = $name;
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		
		//更新交易人的应收款信息以及账户余额
		$sql1 = "UPDATE hb set account_receivable=account_receivable+{$this->params['amount']} WHERE id={$this->params['hb_id']}";
		$this->tool->query($sql1);
		//更新账户余额
		$sql2 = "UPDATE zjzh set remained=remained-{$this->params['amount']} WHERE id={$this->params['zjzh_id']}";
		$this->tool->query($sql2);
		return $ret;
	}
	
	protected function saveOne2One($affectedID, $linkInfo){
		if($linkInfo['table'] == 'zj_pj'){
			if($this->params['zj_fl_id'] == ZJ_FL_PIAOJU)
				$this->tool->update('zj_pj', array('to_yw_id'=>$affectedID), 'id='.$this->params['zj_pj_id']);
		}
		else
			parent::saveOne2One($affectedID, $linkInfo);
	}
}

?>