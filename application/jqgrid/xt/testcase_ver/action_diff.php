<?php
require_once('action_jqgrid.php');

class xt_testcase_ver_action_diff extends action_jqgrid{
	protected function handleGet(){
		$ids = implode(',', json_decode($this->params['vers']));
		$rows = array();
		$sql = "SELECT testcase.code, testcase.summary, testcase_ver.*, edit_status.name as edit_status, ".
			" auto_level.name as auto_level, testcase_priority.name as testcase_priority, GROUP_CONCAT(prj.name) as project".
			" FROM testcase_ver left join testcase on testcase_ver.testcase_id=testcase.id".
			" left join prj_testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id".
			" left join prj on prj_testcase_ver.prj_id=prj.id".
			" left join edit_status on testcase_ver.edit_status_id=edit_status.id".
			" left join auto_level on testcase_ver.auto_level_id=auto_level.id".
			" left join testcase_priority on testcase_ver.testcase_priority_id=testcase_priority.id".
			" where testcase_ver.id in ($ids)".
			" group by testcase_ver.id";			
		$res = $this->db->query($sql);
		// $res = $this->db->query("SELECT * FROM testcase_ver WHERE id IN ($ids)");
		while($row = $res->fetch())
			$rows[' [version '.$row['ver']."]"] = $row;
		$this->renderView("testcase_ver_diff.phtml", array('vers'=>$rows));
	}
}

?>