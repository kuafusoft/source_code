<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
class useradmin_users_action_save extends action_save{
	protected function afterSave($user_id){
		if(isset($this->params['groups_ids'])){
			$this->db->delete('groups_users', "users_id=$user_id");
			foreach($this->params['groups_ids'] as $group_id){
				$this->db->insert('groups_users', array('users_id'=>$user_id, 'groups_id'=>$group_id));
			}
		}
		if(isset($this->params['role_ids'])){
			$this->db->delete('role_user', "user_id=$user_id");
			foreach($this->params['role_ids'] as $role_id){
				$this->db->insert('role_user', array('user_id'=>$user_id, 'role_id'=>$role_id));
			}
		}
	}
}
?>