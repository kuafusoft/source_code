<?php

require_once('action_jqgrid.php');

class xt_zzvw_prj_action_gettctype extends action_jqgrid{	
	public function handlePost(){
		$params = $this->parseParams();
		$res = $this->db->query("SELECT os_id FROM prj WHERE prj.id={$params['value']}");
		$where = "1";
		$os = $res->fetch();
		if(!empty($os['os_id'])){
			$where = "os_ids = ".$os['os_id'];
			if (!empty($params['cond']) && $params['cond'] == 'REGEXP')
				$where = "os_ids REGEXP ".$this->db->quote("^{$os['os_id']}$|^{$os['os_id']},|,{$os['os_id']},|,{$os['os_id']}$");
		}
		$sql = "SELECT id, name FROM testcase_type";
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
}
?>