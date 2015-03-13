<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class action_summary_detail_information extends action_information{
	protected function paramsFor_view_edit($params){
		return parent::paramsFor_view_edit($params);
	}
	
	protected function paramsFor_detail($params){
		$detail_table = $this->get('detail_table');
		if (empty($detail_table))
			$detail_table = $this->get('table').'_detail';
		$view_params = array('container'=>'detail', 'label'=>$this->get('table').' Items', 'db'=>$this->get('db'), 'table'=>$this->get('table'), 'detail_table'=>$detail_table,
			'id'=>$params['id'], 'disabled'=>empty($params['id']), 'view_file_dir'=>'', 'view_file'=>'summary_detail.phtml');

		return $view_params;
	}
}

?>