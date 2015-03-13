<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_node_detail_information.php');

class workflow_prj_action_information extends action_node_detail_information{

	protected function paramsFor_view_edit($params){
		return parent::paramsFor_view_edit($params);
	}
	
	protected function paramsFor_prj_daily_note($params){
		$detail_table = 'daily_note';
		$view_params = array('container'=>'prj_daily_note', 'label'=>'Daily Notes', 'db'=>$this->get('db'), 'table'=>$this->get('table'), 'detail_table'=>$detail_table,
			'id'=>$params['id'], 'disabled'=>empty($params['id']), 'view_file_dir'=>'', 'view_file'=>'summary_detail.phtml');

		return $view_params;
	}

}

?>