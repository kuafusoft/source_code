<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_inactivate extends action_inactivate{
	protected function handlePost(){
		$ret = $this->tool->update($this->get('table'), array('isactive'=>ISACTIVE_INACTIVE),
			"id in (".implode(',', $this->params['id']).") and creater_id = ".$this->userInfo->id, 
			$this->get('db'));
		if(!$ret){
			foreach($this->userInfo->roles as $role){
				if($role == 'admin'){
					$this->tool->update($this->get('table'), array('isactive'=>ISACTIVE_INACTIVE), 
						"id in (".implode(',', $this->params['id']).") and creater_id = ".$this->userInfo->id, 
						$this->get('db'));
				}
			}
		}
	}
}

?>