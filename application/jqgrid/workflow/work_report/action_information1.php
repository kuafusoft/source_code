<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_summary_detail_accordion_information.php');

class workflow_work_report_action_information extends action_summary_detail_accordion_information{
	protected function getDetailParams($params){
		$detail = parent::getDetailParams($params);
		$table = tableDescFactory::get('workflow', 'daily_note');
		$options = $table->getOptions(true);
		$detail['model'] = $options['edit'];
		$detail['view_file_dir'] = 'workflow/work_report';
		return $detail;
	}

	protected function getDetailItems($params){
		$res = $this->db->query("SELECT work_report_detail.* FROM work_report left join work_report_detail on work_report.id=work_report_detail.work_report_id ".
			" WHERE work_report.id={$params['id']}");
		return $res->fetchAll();
	}
}

?>