<?php
	require_once('action_jqgrid.php');
	
	class xt_zzvw_cycle_detail_action_getPriority extends action_jqgrid{
		public function handlePost(){
			//$params = $this->parseParams();
			$params = $this->params;
			$sql = "SELECT DISTINCT testcase_priority.id as id, testcase_priority.name as name FROM cycle_detail".
				" LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id".
				" LEFT JOIN testcase_priority ON testcase_ver.testcase_priority_id=testcase_priority.id";
			$where = "1";
			if(!empty($params['value']) && $params['value']){
				$where = "cycle_detail.cycle_id=".$params['value'];
			}
			$where .= " AND testcase_priority.name is not null";
			$sql .= " WHERE $where ORDER BY testcase_priority.name ASC";
			$res = $this->tool->query($sql);
			return json_encode($res->fetchAll());
		}
	}
?>