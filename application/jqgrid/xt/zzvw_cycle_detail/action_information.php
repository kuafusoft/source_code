<?php
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class xt_zzvw_cycle_detail_action_information extends action_information{
	protected function getViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getViewParams($params);
		$view_params['tabs']['view_edit']['view_file_dir'] = 'xt/'.$this->get('table');
		$view_params['tabs']['view_edit']['id'] = $params['id'];	
		if (!empty($params['cycle_id'])){
			$view_params['tabs']['cycle_detail'] = array('view_file_dir'=>'xt/zzvw_cycle', 'label'=>'Cycle Cases', 'disabled'=>!$params['id']);
			$view_params['tabs']['reports'] = array('label'=>'Reports', 'disabled'=>!$params['id']);
		}			
		return $view_params;
	}
}
?>