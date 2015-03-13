<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_testcase_ver_action_ask2review extends action_save{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = 'askreview.phtml';
		$reviewer_groups = $this->userAdmin->getGroups(null);
		$reviewers = $this->userAdmin->getReviewerList(null);
		$ask2review_comment = "Hi,\n".
			" <Please enter your note here>\n";
		$view_params['reviewer_groups'] = $reviewer_groups;
		$view_params['reviewers'] = $reviewers;
		$view_params['comment'] = $ask2review_comment;
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase_ver';
		return $view_params;
	}
	
	protected function handlePost(){
		$params = $this->params;
		if (!isset($params['reviewers']))
			$params['reviewers'] = array();
		if (!isset($params['reviewer_groups']))
			$params['reviewer_groups'] = array();
		$params['reviewers'] = $this->userAdmin->calcUsers($params['reviewers'], array());// $params['reviewer_groups']);
		$elements = json_decode($params['id']);
		if (is_array($elements))
			$elements = implode(',', $elements);
		//只有Editing状态的Case可以参与Review
		// 得到对应的version id
		if(isset($params['from']) && $params['from'] == 'testcase')
			$cond = " testcase.id in ($elements) AND ver.prj_ids REGEXP ".$this->db->quote($this->tool->genPattern($params['prj_ids']));
		else
			$cond = "ver.id in ({$params['ver']})";
		$sql = "SELECT ver.id, testcase.code, testcase.summary, ver.objective, ver.precondition, ver.expected_result ".
			" FROM testcase left join testcase_ver ver on testcase.id=ver.testcase_id ".
			" WHERE $cond AND ver.edit_status_id=".EDIT_STATUS_EDITING;
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$this->tool->update('testcase_ver', array('edit_status_id'=>EDIT_STATUS_REVIEW_WAITING), "id=".$row['id']);
			$task = array('user_ids'=>$params['reviewers'], 'task_type_id'=>TASK_TYPE_REVIEW, 'url'=>'/jqgrid/jqgrid/oper/review/db/xt/table/testcase_ver/element/'.$row['id'], 
				'controller_id'=>$params['main_reviewer'], 'description'=>'Ask to review the testcase '.$row['code'], 'deadline'=>$params['deadline'], 
				'action_type_id'=>ACTION_TYPE_DIALOG);
			$taskId = $this->userAdmin->addTask($task);//$params['reviewers'], 'review', 'Ask to review the testcase '.$row['code'], '/jqgrid/jqgrid/oper/review/db/xt/table/testcase_ver/element/'.$row['id'], null, 3, 0, $params['main_reviewer']); // 设置一个Review的Task
			foreach($params['reviewers'] as $reviewer){
				$body = $params['ask2review_comment']."\n<BR>". // ATTACH THE DETAIL CASE INFORMATION
					" < The following is machine generated >\n<BR>".
					" Please help to review the testcase, the base information is as following:\n<BR>".
					" Testcase Id:".$row['code']."\n<BR>".
					" Summary:".$row['summary']."\n<BR>".
					" Objective:".$row['objective']."\n<BR>".
					" Precondition: ".$row['precondition']."\n<BR>".
					" Expected Result:".$row['expected_result']."\n<BR><BR>".
					" <a href='javascript:XT.gen_task_dialog({$taskId[$reviewer]}, \"/jqgrid/jqgrid/oper/review/db/xt/table/testcase_ver/element/".$row['id']."\", \"review\")'>Review the Testcase</a>";
			
				$this->userAdmin->inform($reviewer, 'Help to review the testcase', $body);	// 发送一条通知
			}
		}
	}
}
?>