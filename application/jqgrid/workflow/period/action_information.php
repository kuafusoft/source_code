<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_summary_detail_information.php');

class workflow_period_action_information extends action_summary_detail_information{
	protected function init(&$controller){
		parent::init($controller);
		$this->params['detail_table'] = 'daily_note';
	}
}

?>