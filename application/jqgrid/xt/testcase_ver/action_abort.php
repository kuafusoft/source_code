<?php
require_once('action_jqgrid.php');

class xt_testcase_ver_action_abort extends action_jqgrid{
	protected function handlePost(){
		$this->params['id'] = implode(',', json_decode($this->params['id']));
		print_r($this->params);
		$res = $this->tool->query("SELECT GROUP_CONCAT(distinct testcase_id) as testcase_ids FROM testcase_ver WHERE id in ({$this->params['id']})");
		$row = $res->fetch();
		$sql = "DELETE ver, link FROM testcase_ver ver left join prj_testcase_ver link on ver.id=link.testcase_ver_id".
			" where ver.id in ({$this->params['id']}) AND ver.edit_status_id != 1 AND ver.edit_status_id != 2";
		$this->tool->query($sql);
		$sql = "SELECT tc.id, ver.id as ver_id FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id WHERE tc.id in ({$row['testcase_ids']})";
		$res = $this->tool->query($sql);
		$deleteIds = array();
		while($row = $res->fetch()){
			if (empty($row['ver_id']))	//没有Version
				$deleteIds[] = $row['id'];
		}
		if (count($deleteIds)){
			$del = implode(',', $deleteIds);
			$this->tool->delete('testcase', "id in ($del)");
		}
	}
}

?>