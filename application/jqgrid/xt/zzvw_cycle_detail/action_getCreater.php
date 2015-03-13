<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getCreater extends action_jqgrid{

	protected function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$where = "1";
		if($params['value'])
			$where .= " AND cycle_detail.prj_id IN (".$params['value'].")";
		if($params['parent']){
			$res = $this->tool->query("select group_id from cycle where id=".$params['parent']);
			if($info = $res->fetch()){
				if(!empty($info['group_id']))
					$where .= " AND cycle.group_id = ".$info['group_id'];
			}
		}
// print_r($where);
		$res = $this->tool->query("SELECT group_concat(distinct cycle.creater_id) as ids FROM cycle".
			" left join cycle_detail on cycle_detail.cycle_id = cycle.id WHERE ".$where);
		//cycle_type---prj
		if($info = $res->fetch()){
			$userList = $this->userAdmin->getUserList(array('id'=>$info['ids'], 'blank_item'=>true));
		}
		foreach($userList as $k=>$v){
			$users[] = array('id'=>$k, 'name'=>$v);
		}
		return json_encode($users);
	}
}
?>