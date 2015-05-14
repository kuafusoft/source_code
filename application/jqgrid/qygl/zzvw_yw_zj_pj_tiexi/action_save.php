<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_zj_pj_tiexi_action_save extends action_save{
	protected $pj;
	protected function fillDefaultValues($action, &$pair, $db, $table){
// print_r($pair);		
		parent::fillDefaultValues($action, $pair, $db, $table);
		$pair['yw_fl_id'] = YW_FL_PJTX;
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		$res = $this->tool->query("SELECT code, total_money FROM zj_pj WHERE id={$this->params['zj_pj_id']}");
		$this->pj = $res->fetch();
		
		$name = $pair['happen_date'].'向'.$hb['name'].'票据贴息, 票据编号：'.$this->pj['code'].', 面额：'.$this->pj['total_money'].'元';
		$pair['name'] = $name;
// print_r($pair);		
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		//将被贴息或拆分的票据设置为已经使用
		$this->tool->query("UPDATE zj_pj SET to_yw_id=$affectID WHERE id={$this->params['zj_pj_id']}");
		//更新账户余额
		$sql = "UPDATE zjzh set remained=remained-{$this->pj['total_money']} WHERE id={$this->params['zjzh_id']}";
		$this->tool->query($sql);
		$sql = "UPDATE zjzh set remained=remained+{$this->params['amount']}-{$this->params['cost']} WHERE id={$this->params['cash_zjzh_id']}";
		$this->tool->query($sql);
		return $ret;
	}
}

?>