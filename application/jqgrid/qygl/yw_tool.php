<?php 
require_once('const_def_qygl.php');

class yw_tool{
	protected $tool = null;
	public function __construct($tool){
		$this->tool = $tool;
	}
	
	public function getWzItemTemplate($yw_fl_id, $yw_id = 0){
		$value = array();
		$html = '';
		switch($yw_fl_id){
			case YW_FL_SCDJ:
				$out_value = array();
				if(!empty($yw_id)){
					$res = $this->tool->query("SELECT * FROM scdj WHERE yw_id=$yw_id");
					while($row = $res->fetch()){
						$value = $row;
						$r = $this->tool->query("SELECT * FROM pici_detail WHERE pici_id={$value['pici_id']}");
						while($t = $r->fetch()){
							$t['wz_id'] = $value['wz_id'];
							$out_value[] = $t;
						}
					}
				}
				$temp = $this->getTemplate(YW_FL_SCDJ, $value);
				$columnDef = $temp['columnDef'];
				$main = $columnDef;
				unset($main['temp']['wz_id']);
				unset($main['temp']['defect_id']);
				unset($main['temp']['amount']);
				unset($main['temp']['price']);
				unset($main['temp']['ck_weizhi_id']);
				$main['legend'] = '工序';
				$main['editable'] = !$yw_id;
				$main_html = $this->tool->generateEmbed_table($main);

				$out = $columnDef;
				unset($out['temp']['gx_id']);
				// $out['temp']['price'] = array('label'=>'单价', 'name'=>'price', 'post'=>'元/个', 'DATA_TYPE'=>'float', 'type'=>'text');
				$out['value'] = $out_value;
				$out['legend'] = '质量和数量';
				$out['prefix'] = 'scdj_output';
				$out['editable'] = !$yw_id;
				$out_html = $this->tool->multiRowEdit($out);
				
				$input_html = "<fieldset id='fieldset_scdj_input' style='display:none'><legend>使用的原料或零件批次</legend><div id='scdj_input'></div></fieldset>";
				$html = $main_html.$input_html.$out_html;
				break;
				
		
			// case YW_FL_YUNRU:
				// $ruku = $this->singleYWHTML(YW_FL_RUKU);
				// $ret['html'] = $ruku;
				// break;
			// case YW_FL_YUNCHU:
				// $chuku = $this->singleYWHTML(YW_FL_CHUKU);
				// $ret['html'] = $chuku;
				// break;
			// case YW_FL_CG:
			// case YW_FL_JIESHOUDINGDAN:
				// $ret['html'] = $this->singleYWHTML($this->params['value']);
				// break;
			default:
				$temp = $this->getTemplate($yw_fl_id, $value);
				$columnDef = $temp['columnDef'];
				$type = $temp['type'];
				if($type == 'multi_row_edit')
					$html = $this->tool->multiRowEdit($columnDef);
				else
					$html = $this->tool->generateEmbed_table($columnDef);
				break;
		}	
		return $html;
	}
	
	public function getSCDJ_input_html($gx_id){
		//只有分解工序需要人工指定输入的物资和数量，其他工序都可以根据已有信息，按照先进先出的原则自动进行计算
		//需要首先找到组合型产品
		$html = '';
		$comp = array();
		//从工艺表获取生产该产品需要消耗的原料， 从产品表获取产品组成
		$res = $this->tool->query("SELECT * FROM gx where id=$gx_id");
		$gx = $res->fetch();
		$pre_gx_ids = $gx['pre_gx_ids'];
		$defect_id = $gx['defect_id'];
		$gx_fl_id = $gx['gx_fl_id'];
		
		if (empty($pre_gx_ids))
			return '';
		$a_pre_gx_ids = explode(',', $pre_gx_ids);
		$other_pre_gx_ids = array_diff($a_pre_gx_ids, array(GX_CG));
		if(!empty($other_pre_gx_ids)){
			$s_gx_ids = implode(',', $other_pre_gx_ids);
			//查找这些工序生成产品中的组合型产品批次
			$sql = "SELECT pici.wz_id, wz.name FROM pici LEFT JOIN wz on pici.wz_id=wz.id WHERE pici.gx_id in ($s_gx_ids) and wz.zuhe=1";
			$res = $this->tool->query($sql);
			$wzs = $res->fetchAll();
			$comp[] = array('label'=>'输入产品', 'name'=>'input_cp_id', 'DATA_TYPE'=>'int', 'edittype'=>'select', 'editoptions'=>array('value'=>$wzs));
			$comp[] = array('label'=>'数量', 'name'=>'input_cp_amount', 'DATA_TYPE'=>'float');
			$html = $this->tool->_cf($comp, true, null, 2, false, array(), true);
		}
		return $html;
	}
	
	public function getDetailTable($yw_fl_id){
		$data_source_db = 'qygl';
		$data_source_table = '';
		$legend = '';
		switch($yw_fl_id){
			case YW_FL_CG:
			case YW_FL_JIESHOUDINGDAN:
				$data_source_table = 'dingdan';
				$legend = '订单';
				break;
			case YW_FL_YUNRU:
				$data_source_table = 'ruku';
				$legend = '运入物品清单';
				break;
				
			case YW_FL_YUNCHU:
				// $data_source_table = 'yunshu';
				// $legend = '运输';
				$data_source_table = 'chuku';
				$legend = '运出物品清单';
				break;
			case YW_FL_ZJOUT:
				$data_source_table = 'zj_jinchu';
				$legend = '支付';
				break;
			case YW_FL_ZJIN:
				$data_source_table = 'zj_jinchu';
				$legend = '回款';
				break;
			case YW_FL_SCDJ:
				$data_source_table = 'scdj';
				$legend = '生产登记';
				break;
			
		}
		return compact('data_source_db', 'data_source_table', 'legend');
	}
	
	public function getTemplate($yw_fl_id, $value =  array(), $params = array(), $type = 'multi_row_edit'){
		$columnDef = array();
		$ret = $this->getDetailTable($yw_fl_id);
		if(!empty($ret['data_source_table'])){
			$params['yw_fl_id'] = $yw_fl_id;
			$params['label'] = $ret['legend'];
	// print_r($params);		
			if(in_array($yw_fl_id, array(YW_FL_ZJOUT, YW_FL_ZJIN, YW_FL_ZHUANZHANG)))
				$type = 'embed_table';
			if($type == 'multi_row_edit')
				$columnDef = $this->tool->getMultiRowEditTemplate($ret['data_source_db'], $ret['data_source_table'], $value, $params, array('yw_id'), $ret['data_source_table']);
			else
				$columnDef = $this->tool->embed_table($ret['data_source_db'], $ret['data_source_table'], $value, $params, array('yw_id'), $ret['data_source_table']);
		}
		return array('columnDef'=>$columnDef, 'type'=>$type);
	}
	
	public function getDingdanOptions($conditions){//$yw_fl_id, $isactive = ISACTIVE_ACTIVE, $hb_id = 0){
// print_r($conditions);
		$ret = array();
		$sql = "SELECT * from zzvw_dingdan WHERE 1 ";
		$where = "";
		if(!empty($conditions)){
			if(is_array($conditions)){
				foreach($conditions as $k=>$v){
					switch($k){
						case 'yw_fl_id':
							switch($v){
								case YW_FL_CHUKU: //出库
								case YW_FL_YUNCHU: //运出
								case YW_FL_JIESHOUTUIHUO: //接收退货
									$where .= " AND yw_fl_id=".YW_FL_JIESHOUDINGDAN;
									break;
								case YW_FL_RUKU: //入库
								case YW_FL_YUNRU: //运入
								case YW_FL_TUIHUO: //退货
									$where .= " AND yw_fl_id=".YW_FL_CG;
									break;
							}
							break;
						default:
							$where .= " AND $k='$v'";
							break;
					}
				}
			}
			else{
				$where = $conditions;
			}
		}
		$sql .= $where;
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$remained = $row['amount'] - $row['completed_amount'];
			$row['name'] = $row['name']."[订单量：{$row['amount']}, 已完成量：{$row['completed_amount']}, 剩余量：{$remained}]";
			$ret[] = $row;
		}
		return $ret;
	}

	public function genPici($hb_id, $gx_id, $wz_id, $happen_date, $items, $amount){
		//怎样生成批次的名称？
		$name = $happen_date.'-'.date('His').'-'.$hb_id.'-'.$gx_id.'-'.$wz_id;
		$remained = $amount;
		$vp = compact('name', 'hb_id', 'gx_id', 'wz_id', 'amount', 'remained', 'happen_date');
// print_r($vp);		
		$pici_id = $this->tool->insert('pici', $vp);
		foreach($items as $item){
			$item['pici_id'] = $pici_id;
			$item['remained'] = $item['amount'];
// print_r($item);			
			$this->tool->insert('pici_detail', $item);
		}
// print_r($pici_id);
		return $pici_id;
	}
	
	public function ruku(&$data, $rukuyuan_id, $happen_date, $affectID, $jbr_id){ //入库
		//生成批次
// print_r($data);		
		foreach($data as $hb_id=>&$hb_data){
			foreach($hb_data as $wz_id=>&$wz_data){
				foreach($wz_data as $gx_id=>&$gx_data){
// print_r("hb_id = $hb_id, wz_id = $wz_id, gx_id = $gx_id\n");				
					$pici_id = $this->genPici($hb_id, $gx_id, $wz_id, $happen_date, $gx_data['detail'], $gx_data['amount']);
					$gx_data['pici_id'] = $pici_id;
// print_r($pici_id);
					$this->tool->insert('ruku', array('yw_id'=>$affectID, 'pici_id'=>$pici_id)); //生成入库信息
				}
			}
		}
		return $pici_id;
	}
	
	public function chuku($data, $affectID, $rukuyuan_id, $happen_date, $jbr_id){ //出库
		foreach($data as $item){
			$item['yw_id'] = $affectID;
			$this->tool->insert('chuku', $item);
			//更新批次数据
			$this->tool->query("UPDATE pici_detail set remained=remained-{$item['amount']} WHERE id={$item['pici_detail_id']}");
			$this->tool->query("UPDATE pici left join pici_detail on pici.id=pici_detail.pici_id set pici.remained=pici.remained-{$item['amount']}");
		}
	}
	
	public function updateYSK($data, $inc){ //更新应收款信息
		foreach($data as $hb_id=>$amount){
			if($inc)
				$this->tool->query("UPDATE hb SET account_receivable=account_receivable + $amount where id=$hb_id");
			else
				$this->tool->query("UPDATE hb SET account_receivable=account_receivable - $amount where id=$hb_id");
		}
	}
	
	public function preDataForSCDJ($data){
		$gx_id = $data['scdj']['gx_id'];
		$hb_id = $data['hb_id'];
		$output = $data['scdj_output']['data'];
		$pici = array();
		$hb_ysk = array();
		foreach($output as $v){
			if(empty($v['amount']))
				continue;
			if(!isset($hb_ysk[$hb_id])) //应收款
				$hb_ysk[$hb_id] = 0;
			$hb_ysk[$hb_id] += $v['price'] * $v['amount'];
				
			$item = array('defect_id'=>$v['defect_id'], 'amount'=>$v['amount'], 'price'=>$v['price'], 'ck_weizhi_id'=>$v['ck_weizhi_id']);
			if(!isset($pici[$hb_id][$v['wz_id']][$gx_id]))
				$pici[$hb_id][$v['wz_id']][$gx_id] = array('amount'=>0, 'detail'=>array());
			$pici[$hb_id][$v['wz_id']][$gx_id]['amount'] += $v['amount'];
			$pici[$hb_id][$v['wz_id']][$gx_id]['detail'][] = $item;
		}
		return compact('pici', 'hb_ysk');
	}

	public function preDataForYunshu($ck, $yw_fl_id){
		$yunshu = array();
		$pici = array();
		$hb_ysk = array();
		foreach($ck as $v){
			$yunshu_item = array();
			$pici_item = array();
			if(empty($v['amount']))
				continue;
			if(empty($v['gx_id']))
				$v['gx_id'] = GX_FL_CG;
			if(!isset($yunshu[$v['dingdan_id']]))
				$yunshu[$v['dingdan_id']] = array('amount'=>$v['amount']);
			else
				$yunshu[$v['dingdan_id']]['amount'] += $v['amount'];
			$res = $this->tool->query("SELECT * FROM dingdan WHERE id={$v['dingdan_id']}");
			$row = $res->fetch();
			$v['wz_id'] = $row['wz_id'];
			
			if(!isset($hb_ysk[$v['hb_id']])) //应收款
				$hb_ysk[$v['hb_id']] = 0;
			$hb_ysk[$v['hb_id']] += $row['price'] * $row['amount'];
			
			if($yw_fl_id == YW_FL_YUNRU){
				$item = array('defect_id'=>$v['defect_id'], 'amount'=>$v['amount'], 'ck_weizhi_id'=>$v['ck_weizhi_id']);
				if(!isset($pici[$v['hb_id']][$v['wz_id']][$v['gx_id']]))
					$pici[$v['hb_id']][$v['wz_id']][$v['gx_id']] = array('amount'=>0, 'detail'=>array());
				$pici[$v['hb_id']][$v['wz_id']][$v['gx_id']]['amount'] += $v['amount'];
				$pici[$v['hb_id']][$v['wz_id']][$v['gx_id']]['detail'][] = $item;
			}
			else{
				$item = array('pici_detail_id'=>$v['pici_detail_id'], 'amount'=>$v['amount'], 'price'=>$row['price']);
				$pici[] = $item;
			}
		}
		return compact('yunshu', 'pici', 'hb_ysk');
	}
	
	public function genYunshu($data, $yw_id){
		foreach($data as $dingdan_id=>$item){
			$item['yw_id'] = $yw_id;
			$this->tool->insert('yunshu', array('yw_id'=>$yw_id, 'dingdan_id'=>$dingdan_id, 'amount'=>$item['amount']));			
			$this->tool->query("update dingdan SET completed_amount=completed_amount + {$item['amount']} WHERE id=$dingdan_id");
		}
	}
	
	public function zhifu(){ //支付
	
	}
	
	public function huikuan(){ //回款
	
	}
}

?>