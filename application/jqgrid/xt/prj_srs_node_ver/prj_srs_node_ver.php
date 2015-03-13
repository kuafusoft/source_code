<?php

require_once('table_desc.php');

class xt_prj_srs_node_ver extends table_desc{
	public function calcSqlComponents($params, $limited = true){
		$component = parent::calcSqlComponents($params, $limited);
		$component['main']['fields'] .= ",srs_node.code, srs_module.code as srs_module";
		$component['main']['from'] .= " LEFT JOIN srs_node on prj_srs_node_ver.srs_node_id=srs_node.id".
			" LEFT JOIN srs_module on srs_node.srs_module_id=srs_module.id";
		$component['order'] = " srs_module ASC, code ASC";
		return $component;
	}
	
	public function getMoreInfoForRow($row){
		$sql = "SELECT ver.content, ver.ver, ver.update_comment, ver.updated, ver.updater_id, ver.edit_status_id".
			" FROM srs_node_ver ver".
			" WHERE ver.id={$row['srs_node_ver_id']}";
		$res = $this->db->query($sql);
		$more = $res->fetch();
		if (!empty($more))
			$row = array_merge($row, $more);
		
		$sql = "SELECT testcase.code FROM prj_srs_node_testcase link left join testcase on link.testcase_id=testcase.id".
			" WHERE link.prj_id={$row['prj_id']} and link.srs_node_id={$row['srs_node_id']}";
		$res = $this->db->query($sql);
		$row['testcase_cover'] = $res->fetchAll();
		
		$sql = "SELECT ver.content, history.prj_id, history.act, history.created as updated".
			" FROM prj_srs_node_ver_history history LEFT JOIN srs_node_ver ver on history.srs_node_ver_id=ver.id".
			" WHERE history.prj_id={$row['prj_id']} and history.srs_node_id={$row['srs_node_id']}".
			" ORDER BY created DESC";
		$res = $this->db->query($sql);
		$row['history'] = $res->fetchAll();
		return $row;
	}
}
