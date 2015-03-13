<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_getCycle extends action_jqgrid{

	protected function handlePost(){
		$params = $this->parseParams();
		$where = "1";
		if($params['value'])
			$where = "prj_id IN (".$params['value'].")";
		$res = $this->db->query("SELECT id, name FROM cycle WHERE ".$where);
		//cycle_type---prj
		return json_encode($res->fetchAll());
	}
}
?>