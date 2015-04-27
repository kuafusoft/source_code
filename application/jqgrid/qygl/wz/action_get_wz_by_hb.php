<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_wz_action_get_wz_by_hb extends action_jqgrid{
	protected function handlePost(){
		$hb_id = $this->params['value'];
		
		$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
			" FROM hb_wz left join wz on hb_wz.wz_id=wz.id".
			" left join unit on wz.unit_id=unit.id".
			" WHERE hb_wz.hb_id=$hb_id and wz.isactive=1 and wz.id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.")";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
				$wz[] = $row;
		}
		return $wz;
	}
}
