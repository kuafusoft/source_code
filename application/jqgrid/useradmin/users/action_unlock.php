<?php
require_once('action_jqgrid.php');
class useradmin_users_action_unlock extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "unlock.phtml";
		$view_params['view_file_dir'] = '/jqgrid/useradmin/users';

		return $view_params;
	}
	
	protected function handlePost(){
		$ids = implode(',', $this->params['id']);
		$this->db->update('users', array('status_id'=>1), "id in ($ids)");
		return;
	}
}
?>