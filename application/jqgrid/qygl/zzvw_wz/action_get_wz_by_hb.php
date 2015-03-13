<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
class qygl_zzvw_wz_action_get_wz_by_hb extends action_jqgrid{
	protected function handlePost(){
		$ret = $this->getWZ($this->params['value'], $this->params['yw_fl_id']);
		return $ret;
	}
	
	protected function getWZ($hb_id, $yw_fl_id){
		$wz = array();
		switch($yw_fl_id){
			case YW_FL_CG:
				$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
					" FROM gys_wz left join wz on gys_wz.wz_id=wz.id".
					" left join unit on wz.unit_id=unit.id".
					" WHERE gys_wz.hb_id=$hb_id and wz.isactive=1";
				break;
			case YW_FL_JIESHOUDINGDAN:
				$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
					" FROM kh_wz left join wz on gys_wz.wz_id=wz.id".
					" left join unit on wz.unit_id=unit.id".
					" WHERE kh_wz.hb_id=$hb_id and wz.isactive=1";
				break;
			
		}
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$wz[] = $row;
		}
		return $wz;
	}
}
