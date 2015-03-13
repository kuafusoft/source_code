<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_gx_wz_zl_detail_action_getdetail extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
		$sql = "SELECT ck_weizhi_id, price ".
			" FROM gx_wz_zl_detail left join defect on gx_wz_zl_detail.zl_id=defect.zl_id".
			" WHERE gx_id={$this->params['gx']} and wz_id={$this->params['wz']} and defect.id={$this->params['defect']}";
		$res = $this->tool->query($sql);
		if($row = $res->fetch()){
			$ret = $row;
		}
		return $ret;
	}
}
?>
