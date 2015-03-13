<?php
require_once('action_jqgrid.php');

class xt_testcase_ver_action_review extends action_jqgrid{
	protected function handlePost(){
		$params = $this->params;
		$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id={$params['id']}");
		$ver = $res->fetch();
		if($ver['edit_status_id'] == EDIT_STATUS_PUBLISHED || $ver['edit_status_id'] == EDIT_STATUS_GOLDEN)
			return;
			
		$newComments = $this->userInfo->nickname."[".date('Y-m-d H:i:s').": ".$params['submit']."]\n\r<BR>".$params['new_review_comments'];
		$edit_status_id = EDIT_STATUS_REVIEWING;
		$sql = "UPDATE testcase_ver set review_comment=CONCAT(".$this->db->quote($newComments).", \"\n\r<BR>\", review_comment), edit_status_id=$edit_status_id WHERE id=".$params['id'];
//print_r($sql);
		$this->tool->query($sql);
		// inform the testcase owner
		if ($params['submit'] == 'accept' || $params['submit'] == 'reject'){
			$res = $this->tool->query("select testcase.code, testcase.summary, ver.* from testcase_ver ver left join testcase on ver.testcase_id=testcase.id WHERE ver.id=".$params['id']);
			$row = $res->fetch();
			$body = $this->userInfo->nickname." has reviewed the testcase, the result is ".$params['submit'].". <BR>".
				" The testcase information is <BR>".
				" Testcase Id:".$row['code']."<BR>".
				" Summarhy:".$row['summary']."<BR>".
				" Review Comment:<BR>".$params['new_review_comments'];
			$this->userAdmin->inform($row['owner_id'], 'Review Result By '.$this->userInfo->nickname, $body);	// 发送一条通知
		}
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "ver_review.phtml";
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase_ver';
		$userTable = $this->userAdmin->getUserTable();
		$mainSql = "SELECT testcase.code, testcase.summary, edit_status.name as edit_status, auto_level.name as auto_level, testcase_priority.name as testcase_priority, ".
			" testcase_ver.*, group_concat(prj.name) as project, users.nickname as updater".
			" FROM testcase_ver left join testcase on testcase.id=testcase_ver.testcase_id ".
			" left join prj_testcase_ver on prj_testcase_ver.testcase_ver_id=testcase_ver.id".
			" left join prj on prj.id=prj_testcase_ver.prj_id".
			" left join edit_status on edit_status.id=testcase_ver.edit_status_id".
			" left join auto_level on auto_level.id=testcase_ver.auto_level_id".
			" left join testcase_priority on testcase_priority.id=testcase_ver.testcase_priority_id".
			" left join $userTable users on testcase_ver.updater_id=users.id";
		$where = "testcase_ver.id={$params['id']}";
		$group = "testcase_ver.id";
// print_r($mainSql." WHERE ".$where." GROUP BY ".$group);
		$res = $this->tool->query($mainSql." WHERE ".$where." GROUP BY ".$group);
		$testcase_ver = $res->fetch();
		$vers = array('Current Version'=>$testcase_ver);
		if ($testcase_ver['update_from'] != 0){
			$where = "testcase_ver.testcase_id={$testcase_ver['testcase_id']} AND testcase_ver.ver={$testcase_ver['update_from']}";
			$res = $this->tool->query($mainSql." WHERE ".$where." GROUP BY ".$group);
//			$res = $this->db->query("SELECT tc.code, tc.summary, testcase_ver.* FROM testcase_ver left join testcase on testcase.id=testcase_ver.testcase_id WHERE testcase_id={$testcase_ver['testcase_id']} AND ver={$testcase_ver['update_from']}");
			$vers['base_ver'] = $res->fetch();
		}
		$view_params['vers'] = $vers;
		
		$view_params['task_finished'] = false;
		
		// check the task status
		if (!empty($params['task'])){
			$task = $this->userAdmin->getUserTask($params['task']);
			if ($task['task_result_id'] > 0 || $task['user_task_result_id'] > 0)
				$view_params['task_finished'] = true;
		}
//print_r($rows);				
		return $view_params;
	}
}

?>