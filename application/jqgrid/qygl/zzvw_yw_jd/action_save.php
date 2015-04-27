<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_zzvw_yw_jd_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		$res = $this->tool->query("SELECT name FROM hb WHERE id={$pair['hb_id']}");
		$hb = $res->fetch();
		
		$res = $this->tool->query("SELECT name FROM yw_fl WHERE id={$pair['yw_fl_id']}");
		$yw_fl = $res->fetch();
		$name = $hb['name'].'在'.$pair['happen_date'].$yw_fl['name'];
		$pair['name'] = $name;
	}

}

?>