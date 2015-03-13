<?php
require_once('action_jqgrid.php');

class xt_zzvw_prj_action_linkage extends action_jqgrid{
	protected function handlePost(){
// print_r($this->params);
		$sql = "SELECT id, name FROM prj WHERE 1";
		if(!empty($this->params['os_id']))
			$sql .= " AND os_id=".$this->params['os_id'];
		if(!empty($this->params['chip_id']))
			$sql .= " AND chip_id=".$this->params['chip_id'];
		if(!empty($this->params['board_type_id']))
			$sql .= " AND board_type_id=".$this->params['board_type_id'];
		$sql .= " ORDER BY name";
// print_r($sql);
		$res = $this->db->query($sql);
		$rows = $res->fetchAll();
		return json_encode($rows);
	}
	
	
}

?>