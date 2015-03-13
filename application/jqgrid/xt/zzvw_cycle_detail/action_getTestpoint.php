<?php
	require_once('action_jqgrid.php');
	
	class xt_zzvw_cycle_detail_action_getTestpoint extends action_jqgrid{
		public function handlePost(){
			// $params = $this->parseParams();
			// $sql = "SELECT DISTINCT testcase_module.id as id, testcase_module.name as name FROM cycle_detail LEFT JOIN testcase ON cycle_detail.testcase_id=testcase.id LEFT JOIN testcase_module ON testcase.testcase_module_id=testcase_module.id";
			// $where = "1";
			// if(!empty($params['value']) && $params['value']){
				// $where = "cycle_detail.cycle_id=".$params['value'];
			// }
			// $where .= " AND name is not null";
			// $sql .= " WHERE $where ORDER BY name ASC";
			// $res = $this->db->query($sql);
			// return json_encode($res->fetchAll());
		}
	}
?>