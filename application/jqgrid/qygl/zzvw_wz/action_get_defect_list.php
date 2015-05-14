<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_zzvw_wz_action_get_defect_list extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
		$sql = "SELECT defect.id, defect.name from defect left join defect_gx_wz on defect.id=defect_gx_wz.defect_id WHERE wz_id={$this->params['wz_id']} and gx_id={$this->params['gx_id']}";
		$res = $this->tool->query($sql);
		if($row = $res->fetch())
			$ret[] = $row;
		return $ret;
	}
}
