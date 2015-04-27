<?php
require_once("const_def_qygl.php");
class wz_tool{
	protected $tool = null;
	public function __construct($tool){
		$this->tool = $tool;
	}
	
	public function getWZ($conditions, $blankItem = false){
		$wz = array();
		if($blankItem)
			$wz[0] = '';
		$where = $this->tool->generateWhere($conditions);
// print_r($conditions)		;
// print_r($where);
		$res = $this->tool->query("SELECT * FROM wz where $where");
		while($row = $res->fetch())
			$wz[] = $row;
		return $wz;
		return $res->fetchAll();
	}
	
	public function getWZs($hb_id, $yw_fl_id, $withNameOnly = false){
		$wz = array();
		switch($yw_fl_id){
			case YW_FL_CG:
				$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
					" FROM gys_wz left join wz on gys_wz.wz_id=wz.id".
					" left join unit on wz.unit_id=unit.id".
					" WHERE gys_wz.hb_id=$hb_id and wz.isactive=1 and wz.id NOT IN(".WZ_YUNSHU.",".WZ_ZHUANGXIE.")";
				break;
			case YW_FL_JIESHOUDINGDAN:
				$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name".
					" FROM kh_wz left join wz on kh_wz.wz_id=wz.id".
					" left join unit on wz.unit_id=unit.id".
					" WHERE kh_wz.hb_id=$hb_id and wz.isactive=1";
				break;
			case YW_FL_YUNCHU:
			case YW_FL_YUNRU:
			case YW_FL_CHUKU:
			case YW_FL_RUKU:
				//根据订单来选择物资
				$sql = "SELECT DISTINCT wz.id, wz.name, wz.default_price, wz.remained, unit.name as unit_name, dingdan.completed_amount, dingdan.amount".
					" FROM dingdan left join yw on dingdan.yw_id=yw.id".
					" LEFT JOIN wz on dingdan.wz_id=wz.id".
					" LEFT JOIN unit on wz.unit_id=unit.id".
					" WHERE yw.hb_id=$hb_id AND wz.isactive=1 AND dingdan.dingdan_status_id=".DINGDAN_STATUS_ZHIXING;
				break;
			
			
		}
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			if($withNameOnly)
				$wz[] = $row['name'];
			else
				$wz[] = $row;
		}
		return $wz;
	}
}
