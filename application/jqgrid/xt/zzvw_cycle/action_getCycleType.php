<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_getCycleType extends action_jqgrid{

	protected function handlePost(){
		$params = $this->parseParams();
		$where = "1";
		if($params['value'])
			$where = "prj_id IN (".$params['value'].")";
		$res = $this->db->query("SELECT DISTINCT testcase_type.id as id,  testcase_type.name as name FROM cycle LEFT JOIN testcase_type on cycle_type_id=testcase_type.id WHERE ".$where);
		//cycle_type---prj
		return json_encode($res->fetchAll());
	}
}
?>