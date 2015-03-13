<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class workflow_ticket_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		$searchConditions = $params['searchConditions'];
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'workflow.module_ticket.module_ticket':
					$v['field'] = 'workflow.module_ticket.module_id';
					$v['op'] = '=';
					break;
				case 'workflow.ticket_trace.ticket_trace':
					$v['field'] = 'workflow.ticket_trace.content';
					$v['op'] = 'LIKE';
					break;
			}
			
		}
		return $params;
	}
}

?>