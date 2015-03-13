<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getCreaterCycle extends action_jqgrid{

	protected function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$where = "1";
		if($params['value'])
			$where .= " AND cycle.creater_id IN (".$params['value'].")";
		if(!empty($params['os_id']))
			$where .= " AND prj.os_id IN (".$params['os_id'].")";
		if(!empty($params['chip_id']))
			$where .= " AND prj.chip_id IN (".$params['chip_id'].")";
		if(!empty($params['board_type_id']))
			$where .= " AND prj.board_type_id IN (".$params['board_type_id'].")";
		if(!empty($params['prj_id']))
			$where .= " AND cycle_detail.prj_id IN (".$params['prj_id'].")";
		if($params['parent']){
			$res = $this->tool->query("select group_id from cycle where id=".$params['parent']);
			if($info = $res->fetch()){
				if(!empty($info['group_id']))
					$where .= " AND group_id = ".$info['group_id'];
			}
		}
// print_r($where);
		$res = $this->tool->query("SELECT distinct cycle.id, cycle.name FROM cycle".
			" left join cycle_detail on cycle_detail.cycle_id = cycle.id". 
			" left join prj on prj.id = cycle_detail.prj_id".
			" WHERE ".$where);
		//cycle_type---prj
		return json_encode($res->fetchAll());
	}
}
?>