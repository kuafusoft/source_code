<?php
require_once('jqgridmodel.php');

class useradmin_task extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'useradmin';
        $options['table'] = 'task';
            
		$options['columns'] = array(
            'id',
			'task_type_id'=>array('editable'=>false),
			'description'=>array('editable'=>false),
			'task_priority_id'=>array('editable'=>false),
			'deadline'=>array('editable'=>false),
			'progress'=>array('editable'=>false),
			'controller_id'=>array('editable'=>false),
			'task_result_id',
			'comment',
        );
        parent::init($controller, $options);
    } 

	public function modifyTask(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
//print_r($params);			
			$this->db->update('task', array('comment'=>$params['comment'], 'task_result_id'=>$params['task_result_id']));
		}
		else{
			$this->config();
//			$colModels = $this->options['gridOptions']['colModel'];
			
			$view_params = $this->getParamsForInfoTab_view_edit(array('id'=>$params['element']));
            $this->renderView('modify_task.phtml', $view_params);
		}
	}
}

