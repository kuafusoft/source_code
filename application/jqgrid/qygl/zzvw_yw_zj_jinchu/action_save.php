<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_zj_jinchu_action_save extends action_save{
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
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		
		$res = $this->tool->query("SELECT name FROM yw_fl WHERE id={$pair['yw_fl_id']}");
		$yw_fl = $res->fetch();
		$res = $this->tool->query("SELECT * FROM zj_cause WHERE id={$pair['zj_cause_id']}");
		$zj_cause = $res->fetch();
		$name = $hb['name'].'在'.$pair['happen_date'].'因'.$zj_cause['name'].'进行了'.$yw_fl['name'].'';
		$pair['name'] = $name;
		
		
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		
		//更新交易人的应收款信息以及账户余额
		$res = $this->tool->query("SELECT * FROM zj_cause WHERE id={$this->params['zj_cause_id']}");
		$zj_cause = $res->fetch();
		if($zj_cause['zj_direct_id'] == ZJ_DIRECT_OUT){
			$sql1 = "UPDATE hb set account_receivable=account_receivable+{$this->params['amount']} WHERE id={$this->params['hb_id']}";
			$sql2 = "UPDATE zjzh set remained=remained-{$this->params['amount']} WHERE id={$this->params['zjzh_id']}";
		}
		else{
			$sql = "UPDATE hb set account_receivable=account_receivable-{$this->params['amount']} WHERE id={$this->params['hb_id']}";
			$sql2 = "UPDATE zjzh set remained=remained+{$this->params['amount']} WHERE id={$this->params['zjzh_id']}";
		}
		$this->tool->query($sql1);
		$this->tool->query($sql2);
		//如果
		return $ret;
	}
	
}

?>