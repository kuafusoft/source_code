<?php
require_once(APPLICATION_PATH.'/jqgrid/action_export.php');

class workflow_work_report_action_export extends action_export{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/workflow/work_report';
		return $view_params;
	}
}

?>