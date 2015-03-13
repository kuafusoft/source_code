<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_pici_action_get_pici_by_dingdan extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
// print_r($this->params)		;
		if(empty($this->params['gx']) || $this->params['gx'] == 'last')
			$this->params['gx'] =  GX_FL_LAST;
		$sql = "SELECT pici_detail.id, pici.name, pici_detail.defect_id, defect.name as defect, pici_detail.remained".
			" from pici_detail left join pici on pici.id=pici_detail.pici_id ".
			" left join dingdan on dingdan.wz_id=pici.wz_id".
			" left join defect on pici_detail.defect_id=defect.id".
			" WHERE dingdan.id={$this->params['value']} AND pici.gx_id={$this->params['gx']}";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			if(empty($row['defect_id']))
				$row['defect'] = '正品，没有缺陷';
			$row['name'] = $row['name']."[{$row['defect']}] 剩余{$row['remained']}";
			$ret[] = $row;
		}
		return $ret;
	}
}
