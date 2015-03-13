<?php
require_once('action_jqgrid.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_zzvw_prj_action_getboardtype extends action_jqgrid{
	protected function handlePost(){
		$sql = "SELECT DISTINCT board_type.id, board_type.name FROM prj LEFT JOIN board_type ON prj.board_type_id=board_type.id";
		$where = 'board_type.name IS NOT NULL';
		if (!empty($this->params['os_id']))
			$where .= " AND os_id=".$this->params['os_id'];
		if (!empty($this->params['chip_id']))
			$where .= " AND chip_id=".$this->params['chip_id'];
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
}
?>