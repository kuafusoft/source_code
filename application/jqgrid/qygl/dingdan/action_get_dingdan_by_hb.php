<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_dingdan_action_get_dingdan_by_hb extends action_jqgrid{
	protected function handlePost(){
		$dingdan = array();
		$sql = "SELECT * FROM zzvw_dingdan WHERE hb_id={$this->params['value']} and dingdan_status_id={$this->params['status']} and yw_fl_id={$this->params['yw_fl_id']} ";
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$remained = $row['amount'] - $row['completed_amount'];
			$row['name'] = "[{$row['defect']}]的[{$row['wz_name']}]{$row['amount']}{$row['unit_name']}, ".
				"已完成{$row['completed_amount']}{$row['unit_name']}, 尚余===$remained==={$row['unit_name']}";
			$dingdan[] = $row;
		}
		return $dingdan;
	}
}
