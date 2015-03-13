<?php
	require_once('action_jqgrid.php');
	
	class xt_zzvw_cycle_detail_action_getModule extends action_jqgrid{
		public function handlePost(){
			//$params = $this->parseParams();
			$params = $this->params;
			$sql = "SELECT DISTINCT testcase_module.id as id, testcase_module.name as name, testcase_type.name as testcase_type FROM cycle_detail".
				" LEFT JOIN testcase ON cycle_detail.testcase_id=testcase.id".
				" LEFT JOIN testcase_module ON testcase.testcase_module_id=testcase_module.id".
				" LEFT JOIN testcase_type on testcase.testcase_type_id = testcase_type.id";
			$where = "1";
			if(!empty($params['value']) && $params['value']){
				$where = "cycle_detail.cycle_id=".$params['value'];
			}
			$where .= " AND testcase_module.name is not null";
			$sql .= " WHERE $where ORDER BY testcase_module.name ASC";
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
				$module[$row['id']] = $row['name'];
				if(strtolower($row['testcase_type']) == 'fas'){
					if(strtolower($row['name']) == 'fas_trickmodes')
						unset($module[$row['id']]);
print_r($module[$row['id']]);
				}
			}
			return json_encode($module);
		}
	}
?>