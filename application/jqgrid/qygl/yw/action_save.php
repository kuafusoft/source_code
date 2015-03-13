<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_yw_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		$options = $this->getOptions();
		if(empty($this->yw_fl_options)){
			$yw_fl_options = $this->tool->str2array($options['gridOptions']['colModel'][$options['gridOptions']['colModelMap']['yw_fl_id']]['formatoptions']['value']);
		}
		if(empty($this->hb_options)){
			$hb_options = $this->tool->str2array($options['gridOptions']['colModel'][$options['gridOptions']['colModelMap']['hb_id']]['formatoptions']['value']);
		}
		$yw_fl = $yw_fl_options[$pair['yw_fl_id']];
		$hb = $hb_options[$pair['hb_id']];
		$name = $pair['happen_date'].$hb.$yw_fl;
		$pair['name'] = $name;
	}
	
	protected function afterSave($affectID){
// print_r("affectID = $affectID\n");	
		switch($this->params['yw_fl_id']){
			case YW_FL_CG:
			case YW_FL_JIESHOUDINGDAN:
				$this->saveDingdan($affectID);
				break;
			case YW_FL_YUNRU:
			case YW_FL_YUNCHU:
				$this->saveYunshu($affectID);
				break;
			case YW_FL_ZJOUT:
			case YW_FL_ZJIN:
				$this->saveZJ($affectID);
				break;
			case YW_FL_SCDJ:
				$this->saveSCDJ($affectID);
				break;
		}
	}
	
	private function saveSCDJ($affectID){
		$yw_tool = new yw_tool($this->tool);
		$cp = $yw_tool->preDataForSCDJ($this->params);
// print_r($cp);		
		// $pici[$hb_id][$v['wz_id']][$gx_id]['amount'] += $v['amount'];
		// $pici[$hb_id][$v['wz_id']][$gx_id]['detail'][] = $item;
		$pici = $cp['pici'];
		$hb_ysk = $cp['hb_ysk'];
		$yw_tool->updateYSK($hb_ysk, false);
		$yw_tool->ruku($pici, $this->params['helper_id'], $this->params['happen_date'], $affectID, $this->params['jbr_id']);
		//根据不同的工序，需要对库存进行调整
		$gx_input = array();
		$gx_output = array();
		$gx_id = $this->params['scdj']['gx_id'];
		$res = $this->tool->query("SELECT * FROM gx WHERE id=$gx_id");
		$gx = $res->fetch();
		$gx_fl_id = $gx['gx_fl_id'];
		$yl_id = $gx['wz_id']; //成品材质
		$pre_gx_ids = $gx['pre_gx_ids']; //输入来自于哪个工序，只包括产品，不包括原料
		
		//需要哪些原料输入
		$res = $this->tool->query("SELECT * FROM gx_input WHERE gx_id=$gx_id");
		while($t = $res->fetch()){
			$gx_input[$t['wz_id']] = $t;
		}
// print_r($gx_input);
		//有哪些产品之外的输出
		$res = $this->tool->query("SELECT * FROM gx_output WHERE gx_id=$gx_id");
		while($t = $res->fetch()){
			$gx_output[$t['wz_id']] = $t;
		}
		foreach($pici as $hb_id=>$hb_data){
			foreach($hb_data as $wz_id=>$wz_data){
				$res = $this->tool->query("SELECT * FROM wz where id=$wz_id");
				$wz = $res->fetch();
				foreach($wz_data as $gx_id=>$gx_data){
					$amount = $gx_data['amount'];
					$pici_id = $gx_data['pici_id'];
					$scdj_id = $this->tool->insert('scdj', array('yw_id'=>$affectID, 'gx_id'=>$gx_id, 'wz_id'=>$wz_id, 'pici_id'=>$pici_id));
					
					switch($gx_fl_id){
						case GX_FL_ZH: //置换：
							$this->zh($affectID, $scdj_id, $gx, $wz_id, $amount);
							break;
						case GX_FL_ZUHE: //组合：
							$this->zuhe($affectID, $scdj_id, $gx, $wz_id, $amount);
							break;
						case GX_FL_FJ: //分解
							$input_cp_id = $this->params['input_cp_id'];
							$input_cp_amount = $this->params['input_cp_amount'];
							$this->fj($affectID, $scdj_id, $gx, $input_cp_id, $input_cp_amount);
							break;
						case GX_FL_TG: //涂裹
							$this->tg($affectID, $scdj_id, $gx, $wz_id, $amount);
							break;
						case GX_FL_JG: //加工
							$this->jg($affectID, $scdj_id, $gx, $wz_id, $amount);
							break;
					}
					$this->handleGxInput($affectID, $scdj_id, $wz, $amount, $gx_input); //消耗
					$this->handleGxOutput($affectID, $scdj_id, $this->params['hb_id'], $gx_id, $wz, $amount, $gx_output, $this->params['happen_date']); //生成
				}
			}
		}
		
	}
	
	private function zh($affectID, $scdj_id, $gx, $wz_id, $amount){ //置换
		return $this->xiaohao($affectID, $scdj_id, $gx['pre_gx_ids'], $wz_id, $gx['defect_id'], $amount);
	}
	
	private function tg($affectID, $scdj_id, $gx, $wz_id, $amount){ //涂裹
		return $this->xiaohao($affectID, $scdj_id, $gx['pre_gx_ids'], $wz_id, $gx['defect_id'], $amount);
	}
	
	private function jg($affectID, $scdj_id, $gx, $wz_id, $amount){ //加工
		return $this->xiaohao($affectID, $scdj_id, $gx['pre_gx_ids'], $wz_id, $gx['defect_id'], $amount);
	}
	
	private function zuhe($affectID, $scdj_id, $gx, $wz_id, $amount){ //组合
		//从产品定义里获取零配件
		$res = $this->tool->query("SELECT * FROM wz_cp_zuhe WHERE wz_id=$wz_id");
		while($row = $res->fetch()){
			$input_wz_id = $row['input_wz_id'];
			$input_wz_amount = $row['amount'];
			$this->xiaohao($affectID, $scdj_id, $gx['pre_gx_ids'], $input_wz_id, $gx['defect_id'], $amount * $input_wz_amount);
		}
	}
	
	private function fj($affectID, $scdj_id, $gx, $input_cp_id, $input_cp_amount){ //分解
		return $this->xiaohao($affectID, $scdj_id, $gx['pre_gx_ids'], $input_cp_id, $gx['defect_id'], $input_cp_amount);
	}
	
	private function xiaohao($affectID, $scdj_id, $gx_ids, $wz_id, $defect_id, $amount){ //物资消耗
		$warning = array();
		$chuku = array();
		$current = $amount;
		$sql = "SELECT pici.id as pici_id, pici.remained as pici_remained, pici_detail.* FROM pici_detail left join pici on pici.id=pici_detail.pici_id".
			" WHERE pici.wz_id=$wz_id AND pici.gx_id in ($gx_ids) AND pici_detail.defect_id=$defect_id AND pici_detail.remained>0 ".
			" ORDER BY happen_date ASC";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			if($row['remained'] >= $current){
				$chuku[] = array('pici_detail_id'=>$row['id'], 'amount'=>$current);
				$this->tool->insert('scdj_input', array('scdj_id'=>$scdj_id, 'pici_detail_id'=>$row['id'], 'amount'=>$current));
				
				// $remained = $row['remained'] - $current;
				// $pici_remained = $row['pici_remained'] - $current;
				// $this->tool->update('pici_detail', array('remained'=>$remained), "id={$row['id']}");
				// $this->tool->query("UPDATE pici set remained=remained-$current WHERE id={$row['pici_id']}");
				break;
			}
			else{
				$chuku[] = array('pici_detail_id'=>$row['id'], 'amount'=>$row['remained']);
				$this->tool->insert('scdj_input', array('scdj_id'=>$scdj_id, 'pici_detail_id'=>$row['id'], 'amount'=>$row['remained']));
				$current -= $row['remained'];
				// $remained = 0;
				// $this->tool->update('pici_detail', array('remained'=>$remained), "id={$row['id']}");
				// $this->tool->query("UPDATE pici set remained=remained-$current WHERE id={$row['pici_id']}");
			}
		}
		$yw_tool = new yw_tool($this->tool);
		$yw_tool->chuku($chuku, $affectID, 0, '', 0); //出库
		if($current > 0){
			$warning[] = array('wz_id'=>$wz_id, 'remained'=>$current);
		}
		
		return $warning;
	}
	
	private function shengcheng($affectID, $scdj_id, $hb_id, $gx_id, $wz_id, $defect_id, $amount, $happen_date){ //生成
		$yw_tool = new yw_tool($this->tool);
		$ruku = array(
			$hb_id=>array(
				$wz_id=>array(
					$gx_id=>array(
						'amount'=>$amount,
						'detail'=>array(
							array('defect_id'=>$defect_id, 'amount'=>$amount)
						)
					)
				)
			)
		);
		$yw_tool->ruku($ruku, 0, $happen_date, $affectID, 0);
		$this->tool->insert('scdj_output', array('scdj_id'=>$scdj_id, 'pici_id'=>$row['id'], 'amount'=>$amount));
	}
	
	private function calcTotal($wz, $input, $amount){
		$calc_method_id = $input['calc_method_id'];
		switch($calc_method_id){
			case CALC_METHOD_GUDING:
				$total = $amount * $input['amount'];
				break;
			case CALC_METHOD_BMJBL:
				$total = $amount * $input['amount'] * $wz['bmj'];
				break;
			case CALC_METHOD_TJBL:
				$total = $amount * $input['amount'] * $wz['tj'];
				break;
		}
		return $total;
	}
	
	private function handleGxInput($affectID, $scdj_id, $wz, $amount, $gx_input){
		//gx_input，处理同时消耗的原料类物资
		foreach($gx_input as $input_wz_id=>$input){
			$total = $this->calcTotal($wz, $input, $amount);
			$warning = $this->xiaohao($affectID, $scdj_id, '1', $input_wz_id, 1, $total);
		}
	}
	
	private function handleGxOutput($affectID, $scdj_id, $hb_id, $gx_id, $wz, $amount, $gx_output, $happen_date){
		foreach($gx_output as $input_wz_id=>$input){
			$total = $this->calcTotal($wz, $input, $amount);
			$warning = $this->shengcheng($affectID, $scdj_id, $hb_id, $gx_id, $input_wz_id, 1, $total, $happen_date);
		}
	}
	
	private function saveZJ($affectID){
		$zj_pj_id = 0;
		$zj_jinchu = $this->params['zj_jinchu'];
		$zj_jinchu['yw_id'] = $affectID;
		
		$zjzh_id = $this->params['yw_fl_id'] == YW_FL_ZJOUT ? $zj_jinchu['out_zjzh_id'] : $zj_jinchu['in_zjzh_id'];
		// 更新账户信息
		if($this->params['yw_fl_id'] == YW_FL_ZJOUT){//支出
			$this->tool->query("UPDATE zjzh set remained=remained-{$zj_jinchu['amount']} WHERE id=$zjzh_id");
			$this->tool->query("UPDATE hb set account_receivable=account_receivable + {$zj_jinchu['amount']} WHERE id={$this->params['hb_id']}");
		}
		else{//回款
			$this->tool->query("UPDATE zjzh set remained=remained+{$zj_jinchu['amount']} WHERE id=$zjzh_id");
			$this->tool->query("UPDATE hb set account_receivable=account_receivable - {$zj_jinchu['amount']} WHERE id={$this->params['hb_id']}");
		}
		// 检查是否票据
		$res = $this->tool->query("select * from zjzh WHERE id=$zjzh_id");
		$row = $res->fetch();
		if($row['zj_fl_id'] == ZJ_FL_XIANJINZHIPIAO || $row['zj_fl_id'] == ZJ_FL_CHENGDUIHUIPIAO){
			if($this->params['yw_fl_id'] == YW_FL_ZJIN){
				// 在zj_pj中插入一条记录
				$pj = array('code'=>$zj_jinchu['pj_code'], 'expire_date'=>$zj_jinchu['expire_date'], 'from_yw_id'=>$affectID, 'total_money'=>$zj_jinchu['amount']);
				$zj_pj_id = $this->tool->insert("zj_pj", $pj);
			}
			else{
				$this->tool->update('zj_pj', array('to_yw_id'=>$affectID), "id={$zj_jinchu['zj_pj_id']}");
				$zj_pj_id = $zj_jinchu['zj_pj_id'];
			}
		}
		$zj_jinchu['zj_pj_id'] = $zj_pj_id;
		if($this->params['yw_fl_id'] == YW_FL_ZJIN){
			unset($zj_jinchu['pj_code']);
			unset($zj_jinchu['expire_date']);
		}
		$this->tool->insert('zj_jinchu', $zj_jinchu);
	}
	
	private function saveDingdan($affectID){
// print_r($this->params);
		$data = $this->params['dingdan']['data'];
		$params = array('db'=>'qygl', 'table'=>'dingdan');
		$action = actionFactory::get(null, 'save', $params);
		foreach($data as $item){
			$item['yw_id'] = $affectID;
			$item['dingdan_status_id'] = DINGDAN_STATUS_ZHIXING;
			$item = array_merge($item, $params);
			$action->setParams($item);
			$action->handlePost();
			// $this->tool->insert('dingdan', $item);
		}
	}
	
	private function saveYunShu($affectID){
		$yw_tool = new yw_tool($this->tool);
		//更新承运人和装卸人的应收款信息
		$ysk = array();
		$ysk[$this->params['hb_id']] = $this->params['price'] * $this->params['amount'];
		if(!empty($this->params['helper_id']))
			$ysk[$this->params['helper_id']] = $this->params['helper_price'] * $this->params['amount'];
		$yw_tool->updateYSK($ysk, false);
		
		$t = $this->params['yw_fl_id'] == YW_FL_YUNRU ? 'ruku':'chuku';
		$save = actionFactory::get(null, 'save', array('db'=>'qygl', 'table'=>$t));
		foreach($this->params[$t]['data'] as $item){
			$item['yw_id'] = $affectID;
			$item['db'] = 'qygl';
			$item['table'] = $t;
			$save->setParams($item);
			$ret = $save->handlePost();
		}
	}
}

?>