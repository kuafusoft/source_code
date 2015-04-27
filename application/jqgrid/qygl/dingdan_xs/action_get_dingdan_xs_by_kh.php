<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_dingdan_xs_action_get_dingdan_xs_by_kh extends action_jqgrid{
	protected function handlePost(){
		$dingcan_xs = array();
		$kh_id = $this->params['value'];
		$sql = "SELECT DISTINCT dingdan_xs.id, wz.name as wz_name, defect.name as defect, dingdan_xs.wz_id, dingdan_xs.amount, dingdan_xs.completed_amount, unit.name as unit_name, dingdan_xs.defect_id".
			" FROM dingdan_xs left join wz on dingdan_xs.wz_id=wz.id".
			" left join unit on wz.unit_id=unit.id".
			" left join yw_xs on dingdan_xs.yw_xs_id=yw_xs.id".
			" left join defect on dingdan_xs.defect_id=defect.id".
			" WHERE yw_xs.kh_id=$kh_id and wz.isactive=1 and wz.id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.") AND dingdan_xs.dingdan_status_id=".DINGDAN_STATUS_ZHIXING;
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$remained = $row['amount'] - $row['completed_amount'];
			$row['name'] = "{$row['defect']}的{$row['wz_name']}{$row['amount']}{$row['unit_name']}, ".
				"已完成{$row['completed_amount']}{$row['unit_name']}, 尚余===$remained==={$row['unit_name']}";
			$dingcan_xs[] = $row;
		}
		return $dingcan_xs;
	}
}
