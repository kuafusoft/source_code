<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
class action_yw extends action_jqgrid{
	public function getHbInfo($hb_id){
		$res = $this->tool->query("select * from hb where id=$hb_id");
		$row = $res->fetch();
		return $row;
	}
	
    public function getHB($hb_fl_id, $wz_or_work_type = 0){
		$hb = array();
		$main = "SELECT DISTINCT hb.id, hb.name, ht.id as ht_id";
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
			$hb[$row['id']] = $row['name'];
		}
		return $hb;
    }
    
	public function getGYS($wz_id = 0){ //供应商
		return $this->getHB(HB_FL_GYS, $wz_id);
	}
	
	public function getKH($wz_id = 0){ //客户
		return $this->getHB(HB_FL_KH, $wz_id);
	}
	
	public function getYG($work_type_id = 0){ //员工
		return $this->getHB(HB_FL_YG, $work_type_id);
	}
	
	public function getCYR(){  //承运人
		$wz_id = WZ_YUNSHU;
		return $this->getGYS();//$wz_id);
	}
	
	public function getZXR(){  //装卸人
		$wz_id = WZ_ZHUANGXIE;
		return $this->getGYS();//$wz_id);
	}
	
	public function getJBR(){  //经办人
//		$wz_id = WZ_ZHUANGXIE;
		return $this->getYG();
	}
	
	public function getFH_FL(){  //发货方式
		$fh_fl = array();
		$sql = "SELECT * FROM fh_fl";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$fh_fl[$row['id']] = $row['name'];
		}
		return $fh_fl;
	}
	
	protected function getZL(){ //质量等级
		$zl = array();
		$sql = "SELECT * FROM zl";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$zl[$row['id']] = $row['name'];
		}
		return $zl;
	}
	
	protected function getCK(){//仓库
		$zl = array();
		$sql = "SELECT * FROM ck";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$zl[$row['id']] = $row['name'];
		}
		return $zl;
	}
	
	protected function getWZ($wz_id = 0){
		$wz = array();
		$sql = "SELECT wz.*, unit.name as unit_name FROM wz left join unit on wz.unit_id=unit.id where 1";
		if($wz_id > 0)
			$sql .= " AND wz.id=$wz_id";
// print_r($sql);			
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$wz[$row['id']] = $row;
		}
		return $wz;
	}
	
	protected function getZJZH($zj_fl_id = 0){ //资金账户
		$zjzh = array();
		$sql = "SELECT * from zjzh where 1";
		if($zj_fl_id > 0)
			$sql .= " AND zj_fl_id=$zj_fl_id";
		$res = $this->tool->query($sql);
		while($row = $res->fetch())
			$zjzh[$row['id']] = $row['name'];
		return $zjzh;
	}
	
	protected function getZJPJ($conditions = array()){ //票据
		$zj_pj = array();
		$sql = "SELECT * from zj_pj";
		$where = " 1";
		if(!empty($conditions))
			$where = $this->tool->generateWhere($conditions);
		$sql .= " WHERE ".$where;
		$res = $this->tool->query($sql);
		while($row = $res->fetch())
			$zj_pj[$row['id']] = $row['code'];
		return $zj_pj;
	}
	
	protected function yw($yw_fl_id, $params){ //处理业务的公共信息，增加一条记录，返回id
		$params['yw_fl_id'] = $yw_fl_id;
		$yw_id = $this->tool->save($params, 'yw', 'qygl', $is_new);
		return $yw_id;
	}

    /*
    采购：
    涉及的表：
        yw--需要增加一条采购记录
        yw_dingdan--需要增加一条采购对应的详细记录
        wz--更新购入物资的库存量
        hzhb--需要更改Total_money字段
        zjzh--如果有付款，则需要一次支付操作
    需要的参数：
        1. 支付对象：hzhb_id
        2. 支付金额：total_money
        3. 支付日期：happened_date
        4. 对应的采购记录：【】或related_id
        5. 经办人：jbr_id
        6. 付款凭证编号：ticket_no
        7. 付款凭证扫描件：ticket_img
        8. 录入人：creater_id
        9. 采购的物资：wz_id
        10. 采购单价：price
        11. 采购数量：amount
    */
    protected function cg($params){ //向供应商下采购订单，需要修改yw表和yw_dingdan表
		$yw_id = $this->yw(YW_FL_CG, $params);
		
		$yw_dingdan = $params;
		$yw_dingdan['yw_id'] = $yw_id;
// print_r($yw_dingdan)		;
		$yw_dingdan_id = $this->tool->save($yw_dingdan, 'yw_dingdan', 'qygl', $is_new);
		return array($yw_id, $yw_dingdan_id);
    }
    
	protected function xs($params){ //接客户销售订单，需要修改yw表和yw_wz表
		$yw_id = $this->yw(YW_FL_XS, $params);
		
		$yw_dingdan = $params;
		$yw_dingdan['yw_id'] = $yw_id;
		$yw_dingdan_id = $this->tool->save($yw_dingdan, 'yw_dingdan', 'qygl', $is_new);
		return array($yw_id, $yw_dingdan_id);
	}
	
	protected function updateHBReceivableByDingdan($dingdan_id, $amount, $inc){
		$res = $this->tool->query("SELECT yw.hb_id, yw_dingdan.price FROM yw_dingdan left join yw on yw.id=yw_dingdan.yw_id WHERE yw_dingdan.id=$dingdan_id");
		$row = $res->fetch();
		$price = $row['price'];
		return $this->updateHBReceivable($row['hb_id'], $price * $amount, $inc);
	}
	
	protected function updateHBReceivable($hb_id, $amount, $inc){
		$plus = '-';
		if($inc)
			$plus = '+';
		$sql = "UPDATE hb SET account_receivable=account_receivable{$plus}$amount WHERE id=$hb_id";
		$this->tool->query($sql);
	}
	
	/*
	params:
		hb_id
		happen_date
		jbr_id
		dj_id
		note
		
		zj_cause_id
		yw_dingdan_id
		out_zjzh_id
		pj_id
		amount
		cost
	*/
	protected function zf($params){ //支付
// print_r($params);
		$yw_id = $this->yw(YW_FL_ZJOUT, $params);
		$hb = $this->getHbInfo($params['hb_id']);
		
		$yw_zj_jinchu = $params;
		$yw_zj_jinchu['yw_id'] = $yw_id;
		$yw_zj_jinchu_id = $this->tool->save($yw_zj_jinchu, 'yw_zj_jinchu', 'qygl', $is_new);
		//更新账户信息
		$zjzh_id = $params['out_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained-{$params['amount']}-{$params['cost']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		//如果是票据，则还需要更新票据表
		$res = $this->tool->query("SELECT * FROM zjzh WHERE id=$zjzh_id");
		$row = $res->fetch();
		if(in_array($row['zj_fl_id'], array(ZJ_FL_CHENGDUIHUIPIAO, ZJ_FL_XIANJINZHIPIAO))){
			if(!empty($params['zj_pj_id'])){
				$vp = array('to_yw_id'=>$yw_id);
				$vp['note'] = '票据去向：'.$hb['name'];
				$this->tool->update('zj_pj', $vp, "id=".$params['zj_pj_id']);
			}
		}
		//更新合作伙伴应收款信息
		$this->updateHBReceivable($params['hb_id'], $params['amount'], true);

		return array($yw_id, $yw_zj_jinchu_id);
	}
	
	protected function hk($params){ //回款
		$yw_id = $this->yw(YW_FL_HK, $params);
		
		$yw_zj_jinchu = $params;
		$yw_zj_jinchu['yw_id'] = $yw_id;
		$yw_zj_jinchu_id = $this->tool->save($yw_zj_jinchu, 'yw_zj_jinchu', 'qygl', $is_new);
		//更新账户信息
		$zjzh_id = $params['in_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained+{$params['amount']}-{$params['cost']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		//如果是票据，则还需要更新票据表
		$res = $this->tool->query("SELECT * FROM zjzh WHERE id=$zjzh_id");
		$row = $res->fetch();
		if(in_array($row['zj_fl_id'], array(ZJ_FL_CHENGDUIHUIPIAO, ZJ_FL_XIANJINZHIPIAO))){
			$pj = array('from_yw_id'=>$yw_id, 'zj_fl_id'=>$row['zj_fl_id'], 'total_momey'=>$params['amount'], 'code'=>$params['code'], 'expire_date'=>$params['expire_date']);
			$pj['note'] = '票据来自：';
			$this->tool->insert('zj_pj', $pj);
		}
		//更新合作伙伴应收款信息
		$this->updateHBReceivable($params['hb_id'], $params['amount'], false);
		
		return array($yw_id, $yw_zj_jinchu_id);
	}

	protected function zj_zhuanzhang($params){ //内部转账
		$yw_id = $this->yw(YW_FL_ZHUANZHANG, $params);
		
		$yw_zj_jinchu = $params;
		$yw_zj_jinchu['yw_id'] = $yw_id;
		$yw_zj_jinchu_id = $this->tool->save($yw_zj_jinchu, 'yw_zj_jinchu', 'qygl', $is_new);
		//更新账户信息
		$zjzh_id = $params['out_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained-{$params['amount']}-{$params['cost']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		
		$zjzh_id = $params['in_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained+{$params['amount']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		
		return array($yw_id, $yw_zj_jinchu_id);
	}
	
	protected function zj_tiexi($params){ //贴息
		$yw_id = $this->yw(YW_FL_TIEXI, $params);
		
		$yw_zj_jinchu = $params;
		$yw_zj_jinchu['yw_id'] = $yw_id;
		$yw_zj_jinchu_id = $this->tool->save($params, 'yw_zj_jinchu', 'qygl', $is_new);
		//更新账户信息
		$zjzh_id = $params['out_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained-{$params['amount']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		
		$zjzh_id = $params['in_zjzh_id'];
		$sql = "UPDATE zjzh SET remained=remained+{$params['amount']}-{$params['cost']} WHERE id=$zjzh_id";
		$this->tool->query($sql);
		
		//记录汇票信息
		$this->tool->update('zj_pj', array('to_yw_id'=>$yw_id), "id=".$params['zj_pj_id']);
		
		return array($yw_id, $yw_zj_jinchu_id);
	}
	
	/*
	params:
		hb_id
		happen_date
		jbr_id
		dj_id
		note
		
		yw_dingdan_id
		yw_chuku_id
		amount
		price
		dizhi
		huizhi_id
	*/
	protected function yunchu($params){ //运出
		$yw_id = $this->yw(YW_FL_YUNCHU, $params);
		
		$yw_yunchu = $params;
		$yw_yunchu['yw_id'] = $yw_id;
		$yw_yunchu_id = $this->tool->save($yw_yunchu, 'yw_yunchu', 'qygl');
		//更新合作伙伴应收款信息
		$this->updateHBReceivable($params['hb_id'], $params['total'], false);
        return array($yw_id, $yw_yunchu_id);
	}
	
	/*
	params:
		hb_id
		happen_date
		jbr_id
		dj_id
		note
		
		yw_dingdan_id
		amount
		price
	*/
	protected function yunru($params){ //运进
		$yw_id = $this->yw(YW_FL_YUNRU, $params);
		
		$yw_yunru = $params;
		$yw_yunru['yw_id'] = $yw_id;
		$yw_yunru_id = $this->tool->save($yw_yunru, 'yw_yunru', 'qygl');
		
		//更新合作伙伴应收款信息
		$this->updateHBReceivable($params['hb_id'], $params['total'], false);
		
        return array($yw_id, $yw_yunru_id);
	}
	
	protected function xiezai($params){ //卸载
		$yw_id = $this->yw(YW_FL_XIEZAI, $params);
		
		$yw_xiezai = $params;
		$yw_xiezai['yw_id'] = $yw_id;
		$yw_xiezai_id = $this->tool->save($yw_xiezai, 'yw_xiezai', 'qygl');
		
		//更新合作伙伴应收款信息
		$this->updateHBReceivable($params['hb_id'], $params['total'], false);
		
        return array($yw_id, $yw_xiezai_id);
	}
	
	protected function yunshu($params){ //运输
		$yw_id = $this->yw(YW_FL_YUNSHU, $params);
		
		$yw_wz_jinchu = $params;
		$yw_wz_jinchu['yw_id'] = $yw_id;
		$yw_wz_jinchu_id = $this->tool->save($yw_wz_jinchu, 'yw_wz_jinchu', 'qygl');
        return array($yw_id, $yw_wz_jinchu_id);
	}
	
	/*
	params:
		hb_id
		gx_id
		wz_id
		happen_date
		
		detail:
			array(
				zl_id
				defect_id
				amount
			)
		
	*/
	protected function new_pici($params){ //生成批次信息
		$pici = $params;
		unset($pici['detail']);
		$comp = array($params['happen_date'], $params['hb_id'], $params['gx_id'], $params['wz_id']);
		$pici['name'] = implode('-', $comp);
		$pici_id = $this->tool->save($pici, 'pici', 'qygl');
		
		//创建pici_detail记录
		$pici_detail = $params['detail'];
		foreach($pici_detail as $e){
			$e['pici_id'] = $pici_id;
			$e['remained'] = $e['amount'];
			$this->tool->save($e, 'pici_detail', 'qygl');
		}
		
		return $pici_id;
	}
	
	/*
	params:
		hb_id
		gx_id
		wz_id
		happen_date
		yw_dingdan_id
		yw_yunru_id
		yw_xiezai_id
		
		zl_id
		defect_id
		amount
		ck_weizhi_id
	*/
	protected function ruku($params){ //入库
		//生成批次信息
// print_r($params);
		$pici = $params;
		$pici['detail'] = $params['data'];//array('zl_id'=>$params['zl_id'], 'defect_id'=>$params['defect_id'], 'amount'=>$params['amount']);
		$pici_id = $this->new_pici($pici);
		$amount = 0;
		foreach($pici['detail'] as $d){
			$amount += $d['amount'];
		}
		
		//订单号,运输号，卸载号应包括在params里
		$yw_id = $this->yw(YW_FL_RUKU, $params);
		$yw_ruku = $params;
		$yw_ruku['yw_id'] = $yw_id;
		$yw_ruku['pici_id'] = $pici_id;
		$yw_ruku['amount'] = $amount;
		$yw_ruku_id = $this->tool->save($yw_ruku, 'yw_ruku', 'qygl');
		
		//更新物资库存状态
		$this->tool->query("UPDATE wz SET remained=remained+$amount WHERE id={$params['wz_id']}");
		
		//如果是采购环节，则应根据订单信息更新合作伙伴应收款信息
		$dingdan_id = $params['yw_dingdan_id'];
		if(!empty($dingdan_id)){
			$this->updateHBReceivableByDingdan($params['yw_dingdan_id'], $amount, false);
			//更新订单完成情况
			$this->tool->query("UPDATE yw_dingdan SET completed_amount=complete_amount+$amount WHERE id=$dingdan_id");
		}
        return array($yw_id, $yw_ruku_id);
	}
	
	/*
	params:
		hb_id
		gx_id
		wz_id
		happen_date
		yw_dingdan_id
		
		detail:
			array(
				pici_id
				amount
			)
	
	*/
	protected function chuku($params){ //出库
		$yw_id = $this->yw(YW_FL_CHUKU, $params);
		
		$yw_chuku = $params;
		$yw_chuku['yw_id'] = $yw_id;
		$yw_chuku_id = $this->tool->save($yw_ruku, 'yw_chuku', 'qygl');
		//更新批次信息
		$sql = "UPDATE pici_detail set remained=remained-".$params['amount']." WHERE pici_id={$params['pici_id']} AND defect_id={$params['defect_id']}";
		$this->tool->query($sql);
		
		//更新合作伙伴应收款信息
		$this->updateHBReceivableByDingdan($params['yw_dingdan_id'], $params['amount'], true);

        return array($yw_id, $yw_chuku_id);
	}
	
    /*
    生产登记:
        生产过程的数据首先保存在sc_daily里，而不是YW表，主要是因为YW没有维护工序信息，而同一个产品在不同的工序有不同
        形态和价格，用YW表维护似乎不太合适。暂时就决定用YW表维护可销售的产品，而从生产信息表带YW表需要一个入库动作。
        
        涉及的表：
            wz:更新相关物资的库存量，主要是原材料，中间产品的库存情况保存在sc_ck表里
            hzhb:更新Total_money
            sc_daily:记录每天的生产数据
            sc_ck:产品的当前库存
            
        需要的参数信息：
            1. 生产者：hzhb_id
            2. 工序：gx_id 
            3. 产品：wz_id
            4. 数量：good_amount, inferio_amount, bad_amount
            7. 日期：happened_date
            8. 经办人：jbr_id
            9. 录入人：creater_id
        
        处理要点：
            1. 增加产品数量
            2. 对于生产过程中使用的物资减少相应数量      
    */
    public function scdj($params){ //生产登记
        $res = $this->db->query("SELECT * FROM vw_gx_wz_price WHERE gx_id=:gx_id AND wz_id=:wz_id", $params);
        $gx_wz_price = $res->fetch();
        $params['gx_wz_price_history_id'] = $gx_wz_price['gx_wz_price_history_id'];
        $this->db->insert('sc_daily', $params);
        $id = $this->db->lastInsertId();
        // 更新生产库存，检验合格后更新物资库存
        $this->db->query("UPDATE sc_ck SET good_amount=good_amount + :good_amount, inferio_amount=inferio_amount + :inferio_amount WHERE gx_id=:gx_id AND wz_id=:wz_id", $params);
        // 更新工资信息？
//        $this->db->
    }
    
}
