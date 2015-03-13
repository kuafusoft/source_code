<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');
/*
amount	12
happen_date	2014-11-20
hb_id	2
helper_id	4
helper_price	1
jbr_id	3
note	
price	11
ruku[data][0][amount]	12
ruku[data][0][ck_weizhi_i...	1
ruku[data][0][defect_id]	1
ruku[data][0][dingdan_id]	6
ruku[data][0][hb_id]	1
yw_fl_id	2
*/
class qygl_ruku_action_save extends action_save{
	protected function beforeSave($db_name, $table_name, &$params){
		$ret = parent::beforeSave($db_name, $table_name, $params);
		if($ret['code'] == ERROR_OK){
			//应先创建批次
			$res = $this->tool->query("SELECT * FROM dingdan WHERE id={$params['dingdan_id']}");
			if($dingdan = $res->fetch()){
				//更新应收款
				$yw_tool = new yw_tool($this->tool);
				$ysk = array($params['hb_id']=>$dingdan['price'] * $params['amount']);
				$yw_tool->updateYSK($ysk, true);
				//更新订单已完成量
				$this->tool->('dingdan', array('completed_amount'=>$dingdan['completed_amount'] + $params['amount']), "id={$dingdan['id']}");
				//创建批次
				$pici = array('db'=>'qygl', 'table'=>'pici', 'hb_id'=>$params['hb_id'], 'wz_id'=>$dingdan['wz_id'], 'amount'=>$params['amount'], 'gx_id'=>YW_FL_CG);
				$pici_save = actionFactory::get(null, 'save', $pici);
				$pici_ret = $pici_save->handlePost();
				if($pici_ret['code'] == ERROR_OK){
					$params['pici_id'] = $pici_ret['msg'];
					//应创建yunshu记录
					$yunshu = array('yw_id'=>$params['yw_id'], 'dingdan_id'=>$params['dingdan_id'], 'amount'=>$params['amount'], 'db'=>'qygl', 'table'=>'yunshu');
					$action_save = actionFactory::get(null, 'save', $yunshu);
					$ret = $action_save->handlePost();
				}
				else{
					$ret = $pici_ret;
				}
			}
			else{
				$ret['code'] = ERROR_INVALID_DATA;
				$ret['msg'] = 'Invalid dingdan_id:'.$params['dingdan_id'];
			}
		}
		return $ret;
	}
}

?>