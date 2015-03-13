<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
class useradmin_groups_action_save extends action_save{
	protected function _saveOne($db, $table, $pair){
		$groups = $this->tool->extractData($pair, $table, $db);
		$groups_id = parent::_saveOne($db, $table, $groups);
		if(isset($this->params['users_ids'])){
			$this->db->delete('groups_users', "groups_id=$groups_id");
			foreach($this->params['users_ids'] as $user_id){
				$this->db->insert('groups_users', array('users_id'=>$user_id, 'groups_id'=>$groups_id));
			}
		}
		return $groups_id;
    }
}
?>