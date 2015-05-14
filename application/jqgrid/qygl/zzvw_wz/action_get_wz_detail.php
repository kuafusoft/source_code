<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_zzvw_wz_action_get_wz_detail extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
		$sql = "SELECT * from gx_wz_zl_detail WHERE wz_id={$this->params['wz_id']} and gx_id={$this->params['gx_id']} and defect_id={$this->params['defect_id']}";
		$res = $this->tool->query($sql);
		if($row = $res->fetch())
			$ret = $row;
		return $ret;
	}
}
