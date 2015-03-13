<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_testcase_action_save extends action_save{
	protected function _saveOne($db, $table, $pair){
		$case = $this->tool->extractData($pair, 'testcase', 'xt');
		if (isset($pair['node_id'])){
			$case['id'] = $pair['node_id'];
		}

		$vs = $this->tool->extractData($pair, 'testcase_ver', 'xt');
//		if(empty($vs['owner_id']))$vs['owner_id'] = $this->currentUser;
		if(empty($vs['edit_status_id']))$vs['edit_status_id'] = EDIT_STATUS_EDITING;
		if (isset($pair['ver_id']))
			$vs['id'] = $pair['ver_id'];
		else
			$vs['id'] = 0;

		$prj_ids = array();
		if (!empty($pair['prj_ids'])){
			$prj_ids = $pair['prj_ids'];
		}
//print_r($case);		
		// we should create the case first
		$case_id = parent::_saveOne($db, 'testcase', $case);
		$vs['testcase_id'] = $case_id;
		$cover_ver_id = isset($pair['cover_ver_id']) ? $pair['cover_ver_id'] : 0;
		$lastVer = 0;
		
//print_r("cover_ver = $cover_ver_id\n");		
		if ($cover_ver_id > 0)
			$vs['id'] = $cover_ver_id;
		else{
			$needNewVer = false;
			if (!empty($pair['clone_from'])){
				$needNewVer = true;
				$lastVer = $pair['clone_from'];
			}
			else if ($vs['edit_status_id'] == EDIT_STATUS_PUBLISHED || $vs['edit_status_id'] == EDIT_STATUS_GOLDEN){
				$needNewVer = true;
				$lastVer = $vs['id'];
			}
//print_r("needNewVer = $needNewVer, lastVer = $lastVer\n");		
			if ($needNewVer){
				$vs['update_from'] = $vs['ver'];
				$res = $this->db->query("select max(ver) as max_ver FROM testcase_ver WHERE testcase_id=".$vs['testcase_id']);
				$row = $res->fetch();
				$vs['ver'] = $row['max_ver'] + 1;
				$vs['edit_status_id'] = EDIT_STATUS_EDITING;
				unset($vs['id']);
			}
		}
//print_r($vs);	
		$newVer = parent::_saveOne($db, 'testcase_ver', $vs);
		$newPrj = $prj_ids;
		$removePrj = array();
		$res = $this->db->query("SELECT GROUP_CONCAT(prj_id) as prj_ids FROM prj_testcase_ver WHERE testcase_ver_id=$newVer");
		if ($row = $res->fetch()){
			if (!empty($row['prj_ids'])){
				$currentPrjs = explode(',', $row['prj_ids']);
				$newPrj = array_diff($prj_ids, $currentPrjs);
				$removePrj = array_diff($currentPrjs, $prj_ids);
			}
		}
//print_r($prj_ids);
//print_r($newPrj);
//print_r($removePrj);		
		if (!empty($removePrj)){
			$this->db->query("DELETE FROM prj_testcase_ver WHERE testcase_ver_id=$newVer AND prj_id in (".implode(',', $removePrj).")");
		}
		$link = array('testcase_id'=>$case_id, 'testcase_ver_id'=>$newVer, 'owner_id'=>$vs['owner_id'], 'testcase_priority_id'=>$vs['testcase_priority_id'], 'edit_status_id'=>$vs['edit_status_id'], 'auto_level_id'=>$vs['auto_level_id']);
		foreach($newPrj as $prj_id){
			$link['prj_id'] = $prj_id;
			$this->db->insert('prj_testcase_ver', $link);
		}
		if ($newVer != $lastVer && $lastVer != 0){
			//复制Steps
			$sql = "INSERT INTO testcase_ver_step (testcase_ver_id, step_number, description, expected_result, params, auto_level_id, isactive)".
				" SELECT $newVer, step_number, description, expected_result, params, auto_level_id, isactive".
				" FROM testcase_ver_step WHERE testcase_ver_id=".$lastVer;
			$this->db->query($sql);
		}
		return $case_id.":".$newVer;
    }
}
?>