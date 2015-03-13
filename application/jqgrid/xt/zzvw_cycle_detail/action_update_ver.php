<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_update_ver extends xt_zzvw_cycle_detail_action_jqgrid{
	public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$real_table = $this->get('real_table');
		if(!empty($params['id']))
		{
			$element = $this->caclIDs($params);
			if($element == "error")
				return "error";
			$res = $this->tool->query("select id, prj_id, testcase_id from cycle_detail where id in (".implode(",", $element).
				")");
			$i = 0;
			while($row = $res->fetch()){
				$sql = "update cycle_detail detail left join prj_testcase_ver ptv on detail.testcase_id = ptv.testcase_id".
					" LEFT JOIN testcase_ver on testcase_ver.id = ptv.testcase_ver_id".
					" and ptv.prj_id = detail.prj_id".
					" set detail.testcase_ver_id = ptv.testcase_ver_id".
					" where detail.id = {$row['id']}".
					" and detail.cycle_id = {$params['parent']}".
					" and testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")".
					" and ptv.testcase_id = {$row['testcase_id']}".
					" and ptv.prj_id = {$row['prj_id']}";
				$ret = $this->tool->query($sql);
				if($ret)
					$i++;
			}
			if(count($i) == count($element))
				return 'success';
			
		}
		
	}
}
?>