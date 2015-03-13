<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getAssisOwner extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$users = $this->userAdmin->getUsers(implode(",", $params['tester_ids']));
		$testers = array();
		if (!empty($users)){
			$i = 0;
			foreach($users as $user){
				$testers[$i]['id'] = $user['id'];
				$testers[$i++]['name'] = $user['nickname'];
			}
		}
		return json_encode($testers);
	}
}
?>