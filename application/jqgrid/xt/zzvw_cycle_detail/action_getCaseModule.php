<?php
	require_once('action_jqgrid.php');
	class xt_zzvw_cycle_detail_action_getCaseModule extends action_jqgrid{
		public function handlePost(){
			//$params = $this->parseParams();
			$params = $this->params;
			$sql = "SELECT DISTINCT testcase_module.name as id, testcase_module.name as name FROM testcase left join testcase_module on testcase_module.id = testcase.testcase_module_id";
			$where = "1";
			if(!empty($params['value']) && $params['value']){
				$where = "testcase.testcase_type_id =".$params['value'];
			}
			$where .= " AND testcase_module.name is not null";
			$sql .= " WHERE $where ORDER BY name ASC";
			$res = $this->tool->query($sql);
			return json_encode($res->fetchAll());
		}
	}
?>