<?php 
require_once('action_jqgrid.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
class qygl_wz_action_getoutputwz extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
// print_r($this->params);
		$input_wz_id = isset($this->params['input_wz_id']) ? $this->params['input_wz_id'] : 0;
		if(!empty($input_wz_id)){
			$sql = " SELECT wz.*, unit.name as unit FROM wz_cp_zuhe left join wz on wz_cp_zuhe.input_wz_id=wz.id left join unit on wz.unit_id=unit.id WHERE wz_cp_zuhe.wz_id=$input_wz_id";
		}
		else{
			$sql = " SELECT wz.*, unit.name as unit from wz left join unit on wz.unit_id=unit.id where wz.wz_fl_id=".WZ_FL_CHANPIN;
		}
		$res = $this->tool->query($sql);
		while($row = $res->fetch())
			$ret[] = $row;
		return $ret;
	}
}

?>