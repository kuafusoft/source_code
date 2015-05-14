<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_zjzh_action_get_zjzh_by_zj_fl extends action_jqgrid{
	protected function handlePost(){
		$zj_fl_id = $this->params['zj_fl_id'];
		$zjzh = array();
		$sql = "SELECT *".
			" FROM zjzh".
			" WHERE zj_fl_id=$zj_fl_id";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
				$zjzh[] = $row;
		}
		return $zjzh;
	}
}
