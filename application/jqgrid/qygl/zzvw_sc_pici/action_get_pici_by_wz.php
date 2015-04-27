<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_zzvw_sc_pici_action_get_pici_by_wz extends action_jqgrid{
	protected function handlePost(){
		$sc_pici = array();
		$wz_id = $this->params['wz_id'];
		$gx_id = isset($this->params['gx_id']) ? $this->params['gx_id'] : GX_LAST;
		$sql = "SELECT DISTINCT pici.id, pici.name as pici, wz.name as wz_name, defect.name as defect, pici.wz_id, pici.amount, pici.remained, unit.name as unit_name, pici.defect_id".
			" FROM zzvw_sc_pici pici left join wz on pici.wz_id=wz.id".
			" left join unit on wz.unit_id=unit.id".
			" left join defect on pici.defect_id=defect.id".
			" WHERE wz_id=$wz_id and gx_id=$gx_id and wz_id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.")";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$remained = $row['amount'] - $row['completed_amount'];
			// $row['name'] = "批次{$row['pici']}{$row['defect']}的{$row['wz_name']}{$row['remained']}{$row['unit_name']}";
			$row['name'] = "批次：{$row['pici']}, 数量：{$row['remained']}, 缺陷:{$row['defect']}";
			$sc_pici[] = $row;
		}
		return json_encode($sc_pici);
	}
}
