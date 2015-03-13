<?php
require_once('action_jqgrid.php');
class useradmin_users_action_resetpassword extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "resetpassword.phtml";
		$view_params['view_file_dir'] = '/jqgrid/useradmin/users';

		return $view_params;
	}
	
	protected function handlePost(){
		$ids = implode(',', $this->params['id']);//$ids = implode(',', json_decode($this->params['id']));
		$this->db->update('users', array('password'=>md5('123456')), "id in ($ids)");
		return;
	}
}
?>