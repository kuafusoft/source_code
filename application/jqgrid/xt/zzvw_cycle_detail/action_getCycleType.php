<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getCycleType extends action_jqgrid{

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
		$sql = "SELECT DISTINCT testcase_type.id as id, testcase_type.name as name FROM cycle".
			" left join cycle_detail on cycle_detail.cycle_id = cycle.id".
			" LEFT JOIN testcase_type on cycle_type_id=testcase_type.id WHERE ".$where;
		$res = $this->tool->query($sql);
		//cycle_type---prj
		return json_encode($res->fetchAll());
	}
}
?>