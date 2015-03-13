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
class qygl_chuku_action_save extends action_save{
	protected function beforeSave($db_name, $table_name, &$params){
		$ret = parent::beforeSave($db_name, $table_name, $params);
		if($ret['code'] == ERROR_OK){
			$res = $this->tool->query("SELECT * FROM dingdan WHERE id={$params['dingdan_id']}");
			if($dingdan = $res->fetch()){
				//更新应收款
				$yw_tool = new yw_tool($this->tool);
				$ysk = array($params['hb_id']=>$dingdan['price'] * $params['amount']);
				$yw_tool->updateYSK($ysk, false);
				//更新订单已完成量
				$this->tool->update('dingdan', array('completed_amount'=>$dingdan['completed_amount'] + $params['amount']), "id={$dingdan['id']}");
				//更新批次信息
				$this->tool->query("update pici_detail left join pici on pici_detail.pici_id=pici.id set pici.remained=pici.remained-{$params['amount']}, pici_detail.remained=pici_detail.remained-{$params['amount']} where pici_detail.id={$params['pici_detail_id']} ");
				
				//应创建yunshu记录
				$yunshu = array('yw_id'=>$params['yw_id'], 'dingdan_id'=>$params['dingdan_id'], 'amount'=>$params['amount'], 'db'=>'qygl', 'table'=>'yunshu');
				$action_save = actionFactory::get(null, 'save', $yunshu);
				$ret = $action_save->handlePost();
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