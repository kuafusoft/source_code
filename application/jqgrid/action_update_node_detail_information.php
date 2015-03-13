<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_node_detail_information.php');

class action_update_node_detail_information extends action_node_detail_information{
	protected function getViewParams($params){
//print_r($params);	
		$view_params = parent::getTypeTableParams($params['type_id'], $params['id']);
//print_r($view_params);
		return $view_params;
	}
}
?>