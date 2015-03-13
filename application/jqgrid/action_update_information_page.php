<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class action_update_information_page extends action_information{
	protected function getViewParams($params){
		$view_params = $this->getDefaultParamsForView($params);
// print_r($view_params);		
		$page = $this->params['page'];
        $methods = get_class_methods($this);
		$method = 'paramsFor_'.$page;
// print_r($method);		
		if (in_array($method, $methods)){
			$view_params = $this->$method($view_params);
		}
		$view_params['view_file_dir'] = '/jqgrid';
// print_r($view_params);		
		return $view_params;
	}
}
?>