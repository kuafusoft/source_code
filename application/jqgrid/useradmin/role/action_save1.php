<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
class useradmin_role_action_save extends action_save{
	protected function _saveOne($db, $table, $pair){
		$role = $this->tool->extractData($pair, 'role', 'useradmin');
		$role_id = parent::_saveOne($db, 'role', $role);
		if(isset($this->params['users_ids'])){
			$this->db->delete('role_user', "role_id=$role_id");
			foreach($this->params['users_ids'] as $user_id){
				$this->db->insert('role_user', array('user_id'=>$user_id, 'role_id'=>$role_id));
			}
		}
		return $role_id;
    }
}
?>