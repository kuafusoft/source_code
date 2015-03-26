<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class workflow_work_report_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		$searchConditions = $params['searchConditions'];
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'workflow.work_report_detail.work_report_detail':
					$v['field'] = 'workflow.work_report_detail.prj_id';
					$v['op'] = '=';
					break;
			}
			
		}
		return $params;
	}
}

?>