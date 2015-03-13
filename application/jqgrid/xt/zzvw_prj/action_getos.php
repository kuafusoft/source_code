<?php
require_once('action_jqgrid.php');
class xt_zzvw_prj_action_getos extends action_jqgrid{
	protected function handlePost(){
		$params = $this->params;
		$sql = "SELECT DISTINCT os.id, os.name FROM prj LEFT JOIN os ON prj.os_id=os.id";
		$where = 'os.name is not null';
		if (!empty($params['chip_id']))
			$where .= " AND chip_id=".$params['chip_id'];
		if (!empty($params['board_type_id']))
			$where .= " AND board_type_id=".$params['board_type_id'];
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
}
?>