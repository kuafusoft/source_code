<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle/action_information.php');
class xt_zzvw_cycle_action_unfreeze extends xt_zzvw_cycle_action_information{
	public function handlePost(){
		$params = $this->parseParams();
		//$params['id'] = json_decode($params['id']);
		// update the cycle_status
		$ret = $this->tool->update('cycle', array('cycle_status_id'=>CYCLE_STATUS_ONGOING), "id in (".implode(',', $params['id']).") and creater_id = ".$this->userInfo->id);
		if(!$ret){
			foreach($this->userInfo->roles as $role){
				if($role == 'admin')
					$ret = $this->tool->update('cycle', array('cycle_status_id'=>CYCLE_STATUS_ONGOING), "id in (".implode(',', $params['id']).")");
			}
		}
		if(!isset($params['flag']))
			$this->buttons('unfreeze');
//		$this->information_refresh();
	}
}
?>