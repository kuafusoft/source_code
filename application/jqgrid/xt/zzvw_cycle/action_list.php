<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_zzvw_cycle_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		if(!$this->userAdmin->isAdmin($this->userInfo->id)){
			$getGroupFilter = $this->getGroupFilter();
			$params['searchConditions'][] = $getGroupFilter;
		}
		return $params;
	}
		
	private function getGroupFilter(){
		$res = $this->userAdmin->db->query("select distinct groups_id from groups_users where users_id = ".$this->userInfo->id);
		while($row = $res->fetch())
			$info[] = $row['groups_id'];
		$info = implode(",", $info);
		$filter['field'] = 'group_id';
		$filter['op'] = 'in';
		$filter['value'] = $info;
		return $filter;
	}
}

?>