<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class action_ask2review extends action_save{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['view_file'] = 'ask2review.phtml';
		$reviewer_groups = $this->userAdmin->getGroups(null);
		// $reviewers = $this->userAdmin->getReviewerList(null);
		$ask2review_comment = "Hi,\n".
			" <Please enter your note here>\n";
		$view_params['reviewer_groups'] = $reviewer_groups;
		// $view_params['reviewers'] = $reviewers;
		$view_params['comment'] = $ask2review_comment;
		return $view_params;
	}
	
	protected function handlePost(){
		$params = $this->params;
		$db = $params['db'];
		$table = $params['table'];
		if (!isset($params['reviewers']))
			$params['reviewers'] = array();
		if (!isset($params['reviewer_groups']))
			$params['reviewer_groups'] = array();
		$params['reviewers'] = $this->userAdmin->calcUsers($params['reviewers'], array());// $params['reviewer_groups']);
		$this->ask2review($params);
	}
	
	protected function ask2review($params){
		$params['caption'] = $this->table_desc->getCaption();
		$table_desc = tableDescFactory::get($params['db'], $params['table'], array());
		$options = $table_desc->getOptions();
		$ver_links = $options['linkTables']['ver'];
		$keys = array_keys($ver_links);
		$linkInfo = $ver_links[$keys[0]];
// print_r($linkInfo);
		$ver_db = $linkInfo['db'];
		$ver_table = $linkInfo['table'];
		$ver_ids = $params['ver'];
		if(is_array($ver_ids))
			$ver_ids = implode(',', $ver_ids);
		$sql = "select * from $ver_db.$ver_table ver ".
			" WHERE id in ($ver_ids) AND ver.edit_status_id NOT IN (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
// print_r($sql);
		$res = $this->tool->query($sql);
		while($ver = $res->fetch()){
			$this->tool->update("$ver_db.$ver_table", array('edit_status_id'=>EDIT_STATUS_REVIEW_WAITING), "id=".$ver['id']);
			$sql = "SELECT * FROM {$params['db']}.{$params['table']} WHERE id={$ver[$linkInfo['self_link_field']]}";
			$t = $this->tool->query($sql);
			$node = $t->fetch();
			$this->addTask($params, $node, $ver_db, $ver_table, $ver);
		}
	}
	
	protected function addTask($params, $node, $ver_db, $ver_table, $ver){
		$url = "/jqgrid/jqgrid/oper/review/db/{$this->params['db']}/table/{$this->params['table']}/element/{$ver['id']}";
		$displayField = $this->tool->getDisplayField($node);
		$task = array('user_ids'=>$params['reviewers'], 'task_type_id'=>TASK_TYPE_REVIEW, 'url'=>$url, 
			'controller_id'=>$params['main_reviewer'], 'description'=>'Ask to review the '.$params['caption'].' '.$node[$displayField], 'deadline'=>$params['deadline'], 
			'action_type_id'=>ACTION_TYPE_DIALOG);
		$taskId = $this->userAdmin->addTask($task);
		foreach($params['reviewers'] as $reviewer){
			$body = $params['ask2review_comment']."\n<BR>". // ATTACH THE DETAIL CASE INFORMATION
				" < The following is machine generated >\n<BR>".
				" Please help to review the {$params['caption']}, the base information is as following:\n<BR>";
			foreach($node as $k=>$v){
				$body .= "[$k] : $v\n<BR />";
			}
			$body .= " >>>>Version INFORMATION<<<<<\n<BR />";
			foreach($ver as $k=>$v){
				$body .= "[$k] : $v\n<BR />";
			}
			$body .= " <a href='javascript:XT.gen_task_dialog({$taskId[$reviewer]}, \"$url\", \"review\")'>Review the {$params['caption']}</a>";
		
			$this->userAdmin->inform($reviewer, "Help to review the {$params['caption']}: {$node[$displayField]}", $body);	// 发送一条通知
		}
	}
}
?>