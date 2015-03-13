<?php
require_once("const_def_qygl.php");

class hb_tool{
	protected $tool = null;
	public function __construct($tool){
		$this->tool = $tool;
	}
	
    private function getHB($hb_fl_id, $wz_or_work_type = 0, $withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false, $isShiTi = false){//是否实体供应商
		$hb = array();
		if($withBlankItem){
			if($withNameOnly)
				$hb[0] = '';
			else
				$hb[] = array('id'=>0, 'name'=>'');
		}
		$main = "SELECT DISTINCT hb.id, hb.name, hb.account_receivable";
		$from = " hb left join hb_hb_fl on hb.id=hb_hb_fl.hb_id";
		$where = " hb_hb_fl.hb_fl_id=$hb_fl_id and hb.isactive=".ISACTIVE_ACTIVE;
		switch($hb_fl_id){
			case HB_FL_YG: //员工
				if(!empty($wz_or_work_type)){ //工种
					$from .= " LEFT JOIN hb_yg on hb.id=hb_yg.hb_id";
					$where .= " AND hb_yg.work_type_id=$wz_or_work_type";
				}
				break;
			case HB_FL_GYS:
				if(!empty($wz_or_work_type)){ //物资
					$from .= " LEFT JOIN gys_wz on gys_wz.hb_id=hb.id";
					$where .= " AND gys_wz.wz_id=$wz_or_work_type";
				}
				if($isShiTi){
					if(empty($wz_or_work_type)){
						$from .= " LEFT JOIN gys_wz on gys_wz.hb_id=hb.id";
					}
					$from .= " LEFT JOIN wz on gys_wz.wz_id=wz.id";
					$where .= " AND wz.wz_fl_id!=".WZ_FL_FUWU;
				}
				break;
				
			case HB_FL_KH:
				if(!empty($wz_or_work_type)){ //物资
					$from .= " LEFT JOIN kh_wz on kh_wz.hb_id=hb.id";
					$where .= " AND kh_wz.wz_id=$wz_or_work_type";
				}
				break;
			default:
				break;
		}
		$sql = $main." FROM ".$from." WHERE ".$where;
// print_r($sql);		
        $res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$row['name'] = $row['name'];
			if($withReceivableAccount )
				$row['name'] .= " --- [当前应收款:{$row['account_receivable']}元]";
			if($withNameOnly)
				$hb[] = $row['name'];
			else
				$hb[] = $row;
			// $hb[$row['id']] = $row['name'];
		}
		return $hb;
    }
    
	public function getGYS($wz_id = 0, $withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){ //供应商
		return $this->getHB(HB_FL_GYS, $wz_id, $withReceivableAccount, $withNameOnly, $withBlankItem);
	}
	
	public function getSTGYS($wz_id = 0, $withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){ //实体供应商，提供有形的物质，不包括服务
		return $this->getHB(HB_FL_GYS, $wz_id, $withReceivableAccount, $withNameOnly, $withBlankItem, true);
	}
	
	public function getKH($wz_id = 0, $withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){ //客户
		return $this->getHB(HB_FL_KH, $wz_id, $withReceivableAccount, $withNameOnly, $withBlankItem);
	}
	
	public function getYG($work_type_id = 0, $withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){ //员工
		return $this->getHB(HB_FL_YG, $work_type_id, $withReceivableAccount, $withNameOnly, $withBlankItem);
	}
	
	public function getCYR($withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){  //承运人
		$wz_id = WZ_YUNSHU;
		return $this->getGYS($wz_id, $withReceivableAccount, $withNameOnly, $withBlankItem);//$wz_id);
	}
	
	public function getZXR($withReceivableAccount = true, $withNameOnly = false, $withBlankItem = false){  //装卸人
		$wz_id = WZ_ZHUANGXIE;
		return $this->getGYS($wz_id, $withReceivableAccount, $withNameOnly, $withBlankItem);
	}
	
	public function getJBR(){  //经办人
//		$wz_id = WZ_ZHUANGXIE;
		return $this->getYG(0, 0);
	}
	
	public function getJRXGR(){ //金融相关人
		return $this->getHB(HB_FL_JRXGR);
	}
}
