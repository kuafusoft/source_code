<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_pici_scdj_action_save extends action_save{
	protected function prepare($db, $table, $pair){
// print_r($pair);
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		$res = $this->tool->query("SELECT name FROM gx WHERE id={$pair['gx_id']}");
		$gx = $res->fetch();
		$res = $this->tool->query("SELECT name, unit_name FROM zzvw_wz WHERE id={$pair['wz_id']}");
		$wz = $res->fetch();
		$res = $this->tool->query("SELECT name FROM defect WHERE id={$pair['defect_id']}");
		$defect = $res->fetch();
		
		$name = $pair['happen_date'].','.$hb['name'].'在'.$gx['name'].'生产了'.$pair['amount'].$wz['unit_name'].$defect['name'].'的'.$wz['name'];
		$pair['name'] = $name;
		
		$pair = parent::prepare($db, $table, $pair);
		return $pair;
	}
}

?>