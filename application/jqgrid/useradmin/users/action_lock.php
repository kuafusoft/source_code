<?php
require_once('action_jqgrid.php');
class useradmin_users_action_lock extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "lock.phtml";
		$view_params['view_file_dir'] = '/jqgrid/useradmin/users';

		return $view_params;
	}
	
	protected function handlePost(){
print_r($this->params)	;
		$ids = implode(',', $this->params['id']);
		$this->db->update('users', array('status_id'=>2), "id in ($ids)");
		return;
	}
}
?>