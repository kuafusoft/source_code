<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_wz_action_get_wz_by_kh extends action_jqgrid{
	protected function handlePost(){
		$kh_id = $this->params['value'];
		
		$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
			" FROM kh_wz left join wz on kh_wz.wz_id=wz.id".
			" left join unit on wz.unit_id=unit.id".
			" WHERE kh_wz.kh_id=$kh_id and wz.isactive=1 and wz.id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.")";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
				$wz[] = $row;
		}
		return $wz;
		$tool = new wz_tool($this->tool);
		$ret = $tool->getWZs($this->params['value'], 1);
		return $ret;
	}
}
