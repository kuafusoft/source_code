<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getrel extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$where = "1";
		if($params['value'])
			$where = "os_id=".$params['value'];
		$sql = "SELECT id, name FROM rel WHERE ".$where;
		$res = $this->tool->query($sql);
		$rel = $res->fetchAll();
		return $rel;
	}
}
?>