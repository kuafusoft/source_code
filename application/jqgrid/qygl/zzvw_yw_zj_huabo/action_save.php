<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_zj_huabo_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		$pair['yw_fl_id'] = YW_FL_ZJHB;
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		$res = $this->tool->query("SELECT name FROM zjzh WHERE id={$this->params['out_zjzh_id']}");
		$out = $res->fetch();
		$res = $this->tool->query("SELECT name FROM zjzh WHERE id={$this->params['in_zjzh_id']}");
		$in = $res->fetch();
		
		$name = $pair['happen_date'].'从'.$out['name'].'向'.$in['name'].'划拨资金'.$this->params['amount'].'元';
		$pair['name'] = $name;
	}

	protected function afterSave($affectID){
		$ret = parent::afterSave($affectID);
		//更新账户余额
		$sql = "UPDATE zjzh set remained=remained-{$this->params['amount']}-{$this->params['cost']} WHERE id={$this->params['out_zjzh_id']}";
		$this->tool->query($sql);
		$sql = "UPDATE zjzh set remained=remained+{$this->params['amount']} WHERE id={$this->params['in_zjzh_id']}";
		$this->tool->query($sql);
		return $ret;
	}
}

?>