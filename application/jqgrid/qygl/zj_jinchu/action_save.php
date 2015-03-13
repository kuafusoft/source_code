<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');
/*
amount	12
chuku[data][0][amount]	12
chuku[data][0][dingdan_id...	3
chuku[data][0][hb_id]	6
chuku[data][0][pici_detai...	2
happen_date	2014-11-20
hb_id	2
helper_id	4
helper_price	1
jbr_id	3
note	
price	11
yw_fl_id	14
*/
class qygl_zj_jinchu_action_save extends action_save{
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

}

?>