<?php
	require_once('action_jqgrid.php');
	
	class xt_zzvw_cycle_detail_action_getAutoLevel extends action_jqgrid{
		public function handlePost(){
			//$params = $this->parseParams();
			$params = $this->params;
			$sql = "SELECT DISTINCT auto_level.id as id, auto_level.name as name FROM cycle_detail LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id LEFT JOIN auto_level ON testcase_ver.auto_level_id=auto_level.id";
			$where = "1";
			if(!empty($params['value']) && $params['value']){
				$where = "cycle_detail.cycle_id=".$params['value'];
			}
			$where .= " AND name is not null";
			$sql .= " WHERE $where ORDER BY name ASC";
			$res = $this->tool->query($sql);
			return json_encode($res->fetchAll());
		}
	}
?>