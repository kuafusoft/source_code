<?php 
require_once('action_jqgrid.php');
require_once('const_def_qygl.php');

class qygl_dingdan_xs_action_change_status extends action_jqgrid{
	protected function handlePost(){
		$dingdan_status_id = DINGDAN_STATUS_ZHIXING;
		switch($this->params['status']){
			case 'qx':
				$dingdan_status_id = DINGDAN_STATUS_QUXIAO;
				break;
			case 'jieshu':
				$dingdan_status_id = DINGDAN_STATUS_JIESHU;
				break;
		}
		$strIds = implode(',', $this->params['id']);
		$this->tool->update('dingdan_xs', array('dingdan_status_id'=>$dingdan_status_id), "id IN ($strIds)", 'qygl');
		return;
	}
}

?>