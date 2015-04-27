<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_dingdan_cg_action_get_dingdan_cg_by_gys extends action_jqgrid{
	protected function handlePost(){
		$dingcan_cg = array();
		$gys_id = $this->params['value'];
		$sql = "SELECT DISTINCT dingdan_cg.id, wz.name as wz_name, defect.name as defect, dingdan_cg.wz_id, dingdan_cg.amount, dingdan_cg.completed_amount, unit.name as unit_name, dingdan_cg.defect_id".
			" FROM dingdan_cg left join wz on dingdan_cg.wz_id=wz.id".
			" left join unit on wz.unit_id=unit.id".
			" left join yw_cg on dingdan_cg.yw_cg_id=yw_cg.id".
			" left join defect on dingdan_cg.defect_id=defect.id".
			" WHERE yw_cg.gys_id=$gys_id and wz.isactive=1 and wz.id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.") AND dingdan_cg.dingdan_status_id=".DINGDAN_STATUS_ZHIXING;
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$remained = $row['amount'] - $row['completed_amount'];
			$row['name'] = "{$row['defect']}的{$row['wz_name']}{$row['amount']}{$row['unit_name']}, ".
				"已完成{$row['completed_amount']}{$row['unit_name']}, 尚余===$remained==={$row['unit_name']}";
			$dingcan_cg[] = $row;
		}
		return $dingcan_cg;
	}
}
