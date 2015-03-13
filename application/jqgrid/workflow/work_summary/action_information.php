<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_summary_detail_accordion_information.php');

class workflow_work_summary_action_information extends action_summary_detail_accordion_information{
	protected function getDetailParams($params){
		$detail = parent::getDetailParams($params);
		$table = tableDescFactory::get('workflow', 'daily_note');
		$options = $table->getOptions(true);
		$detail['model'] = $options['edit'];
		$detail['view_file_dir'] = 'workflow/work_summary';
		return $detail;
	}

	protected function getDetailItems($params){
		$res = $this->db->query("SELECT daily_note.* FROM work_summary left join work_summary_detail on work_summary.id=work_summary_detail.work_summary_id ".
			" left join daily_note on work_summary_detail.daily_note_id=daily_note.id WHERE work_summary.id={$params['id']}");
		return $res->fetchAll();
	}
}

?>