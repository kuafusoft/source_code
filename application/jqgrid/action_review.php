<?php
require_once('action_jqgrid.php');

class action_review extends action_jqgrid{
	protected function handlePost(){
		$params = $this->params;
		$node = tableDescFactory::get($params['db'], $params['table'], array());
		$options = $node->getOptions();
		$linkInfo = current($options['linkTables']['ver']);
		
		$res = $this->tool->query("SELECT * FROM {$linkInfo['db']}.{$linkInfo['table']} WHERE id={$params['id']}");
		$ver = $res->fetch();
		if($ver['edit_status_id'] == EDIT_STATUS_PUBLISHED || $ver['edit_status_id'] == EDIT_STATUS_GOLDEN)
			return;
		if(empty($params['new_review_comments']))
			$params['new_review_comments'] = '';
		$newComments = $this->userInfo->nickname."[".date('Y-m-d H:i:s').": ".$params['submit']."]\n\r<BR>".$params['new_review_comments'];
		$edit_status_id = EDIT_STATUS_REVIEWING;
		$sql = "UPDATE {$linkInfo['db']}.{$linkInfo['table']} set review_comment=CONCAT(".$this->db->quote($newComments).", \"\n\r<BR>\", review_comment), edit_status_id=$edit_status_id WHERE id=".$params['id'];
print_r($sql);
		$this->tool->query($sql);
		// inform the testcase owner
		if ($params['submit'] == 'accept' || $params['submit'] == 'reject'){
			$body = $this->userInfo->nickname." has reviewed the {$params['node_caption']} {$params['node_name']}, the result is ".$params['submit'].". <BR>".
				" Review Comment:<BR>".$params['new_review_comments'];
			$this->userAdmin->inform($ver['owner_id'], 'Review Result By '.$this->userInfo->nickname, $body);	// 发送一条通知
		}
	}
	
	protected function getViewParams($params){
// print_r($params);	
		$view_params = $params;
		$view_params['view_file'] = "ver_review.phtml";
		$view_params['view_file_dir'] = '/jqgrid';
		$userTable = $this->userAdmin->getUserTable();
		$node = tableDescFactory::get($params['db'], $params['table'], array());
		$options = $node->getOptions();
		$linkInfo = current($options['linkTables']['ver']);
		$res = $this->tool->query("SELECT * FROM {$linkInfo['db']}.{$linkInfo['table']} WHERE id={$params['id']}");
		$ver = $res->fetch();
		$node_id = $ver[$linkInfo['self_link_field']];
		$update_from = $ver['update_from'];
// print_r($node_id);
		
		$list_params = array('db'=>$params['db'], 'table'=>$params['table'], 'id'=>$node_id);
		$action_list = actionFactory::get($this->controller, 'list', $list_params);
		$action_list->setParams($list_params);
		$ret = $action_list->handlePost();
		$node_value = $ret['rows'][0];
		$displayField = $this->tool->getDisplayField($node_value);
		$node_name = $node_value[$displayField];
		$node_caption = $node->getCaption();
		
// print_r($node_value);
		$node_edit_fields = $options['edit'];
// print_r($node_edit_fields);
		
		$ver = tableDescFactory::get($linkInfo['db'], $linkInfo['table'], array());
		$ver_options = $ver->getOptions();
		$ver_edit_fields = $ver_options['edit'];
		
		$ver_list_params = array('db'=>$linkInfo['db'], 'table'=>$linkInfo['table'], 'id'=>$params['id']);
		$ver_action_list = actionFactory::get($this->controller, 'list', $ver_list_params);
		$ver_action_list->setParams($ver_list_params);
		$ret = $ver_action_list->handlePost();
		$ver_value['Current Version'] = array_merge($node_value, $ret['rows'][0]);
// print_r($ret);		
		if($update_from > 0){
			unset($ver_list_params['id']);
			$ver_list_params['ver'] = $update_from;
			$ver_list_params[$linkInfo['self_link_field']] = $node_id;
			$ver_action_list->setParams($ver_list_params);
			$ret = $ver_action_list->handlePost();
			$ver_value['Base Version'] = array_merge($node_value, $ret['rows'][0]);
		}
		
		$view_params['vers'] = $ver_value;
		$view_params['edit_options'] = array_merge($node_edit_fields, $ver_edit_fields);
		$view_params['node_name'] = $node_name;
		$view_params['node_caption'] = $node_caption;
		
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