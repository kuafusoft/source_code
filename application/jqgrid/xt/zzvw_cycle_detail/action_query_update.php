<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_query_update extends xt_zzvw_cycle_detail_action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$real_table = $this->get('real_table');
		$where = 'cycle_id = '.$params['parent'];
		if(!empty($params['id']))
		{
			$element = $this->caclIDs($params);
			if($element == "error")
				return "error";
			$where .= " AND id in (".implode(",", $element).")";
		}
		$sql = "select distinct cycle.prj_id as prj_id from cycle_detail detail".
			" left join cycle on detail.cycle_id = cycle.id".
			" where {$where}";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$ver_sql = "update cycle_detail detail left join prj_testcase_ver ptv on detail.testcase_id = ptv.testcase_id".
				" set detail.testcase_ver_id = ptv.testcase_ver_id".
				" where detail.cycle_id = {$params['parent']} and ptv.prj_id = {$row['prj_id']}";
			$this->db->query($ver_sql);
		}
		return 'success';
	}
}
?>