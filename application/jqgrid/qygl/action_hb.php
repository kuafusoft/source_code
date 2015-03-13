<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
class qygl_hb_action_hb extends action_jqgrid{
    private function getHB($hb_fl_id, $wz_or_work_type = 0){
		$hb = array();
		$main = "SELECT DISTINCT hb.id, hb.name, hb.account_receivable, ht.id as ht_id";
		$from = " hb left join ht on ht.hb_id=hb.id";
		$where = " 1 and hb.isactive=".ISACTIVE_ACTIVE;
		switch($hb_fl_id){
			case HB_FL_YG: //员工
				$where .= " AND ht.ht_fl_id=".HT_FL_LD." and ht.isactive=".ISACTIVE_ACTIVE." and ht.id is not null";
				if(!empty($wz_or_work_type)){ //工种
					$from .= " LEFT JOIN ht_ld on ht_ld.ht_id=ht.id";
					$where .= " AND ht_ld.work_type_id=$wz_or_work_type";
				}
				break;
			case HB_FL_GYS:
				$where .= " AND ht.ht_fl_id=".HT_FL_CG." and ht.isactive=".ISACTIVE_ACTIVE." and ht.id is not null";
				if(!empty($wz_or_work_type)){ //物资
					$from .= " LEFT JOIN ht_item on ht_item.ht_id=ht.id";
					$where .= " AND ht_item.wz_id=$wz_or_work_type AND ht_item.isactive=".ISACTIVE_ACTIVE;
				}
				break;
				
			case HB_FL_KH:
				$where .= " AND ht.ht_fl_id=".HT_FL_XS." and ht.isactive=".ISACTIVE_ACTIVE." and ht.id is not null";
				if(!empty($wz_or_work_type)){ //物资
					$from .= " LEFT JOIN ht_item on ht_item.ht_id=ht.id";
					$where .= " AND ht_item.wz_id=$wz_or_work_type AND wz_item.isactive=".ISACTIVE_ACTIVE;
				}
				break;
			default:
				break;
		}
		$sql = $main." FROM ".$from." WHERE ".$where;
// print_r($sql);		
        $res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$row['name'] = $row['name']." --- [当前应收款:{$row['account_receivable']}元]";
			$hb[] = $row;
			// $hb[$row['id']] = $row['name'];
		}
		return $hb;
    }
    
	protected function getGYS($wz_id = 0){ //供应商
		return $this->getHB(HB_FL_GYS, $wz_id);
	}
	
	protected function getKH($wz_id = 0){ //客户
		return $this->getHB(HB_FL_KH, $wz_id);
	}
	
	protected function getYG($work_type_id = 0){ //员工
		return $this->getHB(HB_FL_YG, $work_type_id);
	}
	
	protected function getCYR(){  //承运人
		$wz_id = WZ_YUNSHU;
		return $this->getGYS();//$wz_id);
	}
	
	protected function getZXR(){  //装卸人
		$wz_id = WZ_ZHUANGXIE;
		return $this->getGYS();//$wz_id);
	}
	
	protected function getJBR(){  //经办人
//		$wz_id = WZ_ZHUANGXIE;
		return $this->getYG();
	}
}
