<?php
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class useradmin_task_action_modifyTask extends action_information{
	protected function handleGet(){
		$view_params = $this->getViewParams($this->params);
		$this->renderView($view_params['view_file'], $view_params, $view_params['view_file_dir']);
	}

	protected function handlePost(){
		$params = $this->params;
		$this->db->update('task', array('comment'=>$params['comment'], 'task_result_id'=>$params['task_result_id']), "id=".$params['id']);
	}
	
	protected function getViewParams($params){
		$view_params = $this->paramsFor_view_edit(array('db'=>$params['db'], 'table'=>$params['table'], 'id'=>$params['id']));
		$view_params['view_file'] = "modify_task.phtml";
		$view_params['view_file_dir'] = '/jqgrid/useradmin/task';
// print_r($view_params);
		$result_model = array();
		$result_value = array();
		$fields = array('task_type_id', 'description', 'task_priority_id', 'deadline', 'progress');
		foreach($fields as $f)
			$model[$f] = $view_params['node']['model'][$f];

		// get the reviewer information
		$i = 0;
		$res = $this->db->query("SELECT users.nickname, task_result.name as result, user_task.comment FROM user_task left join users on user_task.user_id=users.id left join task_result on task_result.id=user_task.task_result_id where task_id=".$params['id']);
		while($row = $res->fetch()){
			$name = 'user_result_' + $i;
			$result_value[$name] = $row['result'];
			$result_model[$name] = array('label'=>$row['nickname'], 'name'=>$name, 'type'=>'text', 'editable'=>false, 'value'=>$row['result']);
			$i ++;
// print_r($model[$name]);
			$name = 'user_comment_' + $i;
			$result_value[$name] = $row['comment'];
			$result_model[$name] = array('label'=>'Comment', 'name'=>$name, 'type'=>'textarea', 'editable'=>false, 'value'=>$row['comment']);
			$i ++;
		}
		
		$result_model['task_result_id'] = $view_params['node']['model']['task_result_id'];
		$result_model['comment'] = $view_params['node']['model']['comment'];
		
		unset($view_params['model']['task_result_id']);
		unset($view_params['model']['comment']);

		$view_params['result_model'] = $result_model;
		$view_params['result_value'] = $result_value;
		// print_r($view_params);
		return $view_params;
	}
}

?>