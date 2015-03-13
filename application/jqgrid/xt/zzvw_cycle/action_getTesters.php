<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getTesters extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$where = "1";
		if($params['value'])
			$where = "id=".$params['value'];
		$sql = "SELECT tester_ids FROM cycle WHERE ".$where;
		$res = $this->tool->query($sql);
		$tester = $res->fetch();
		$tester_ids = explode(",", $tester['tester_ids']);
		foreach($tester_ids as $key=>$val){
			if(empty($val))
				unset($tester_ids[$key]);
		}
		$tester['tester_ids'] = implode(",", $tester_ids);
		$users = $this->userAdmin->getUsers($tester['tester_ids']);
		$testers = array();
		if (!empty($users)){
			$i = 0;
			foreach($users as $user){
				$testers[$i]['id'] = $user['id'];
				$testers[$i++]['name'] = $user['nickname'];
			}
		}
		return $testers;
	}
}
?>