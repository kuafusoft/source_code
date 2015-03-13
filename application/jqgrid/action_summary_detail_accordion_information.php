<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class action_summary_detail_accordion_information extends action_information{
	protected function paramsFor_view_edit($params){
		$view_params = parent::paramsFor_view_edit($params);
		$view_params['view_file'] = 'summary_detail_accordion.phtml';
		$view_params['detail'] = $this->getDetailParams($params);
		return $view_params;
	}
	
	protected function getDetailParams($params){
// print_r($params);	
		$detail['items'] = $this->getDetailItems($params);
		$detail['legend'] = $this->get('table').' Items';
		$detail['view_file'] = 'accordion.phtml';
		$detail['view_file_dir'] = '';
		
		return $detail;
	}
	
	protected function getDetailItems($params){
		$summary = $this->get('table');
		$detail_table = $this->get('detail_table');
		if (empty($detail_table))
			$detail_table = $this->get('table').'_detail';
		$sql = "SELECT $detail_table.* FROM $summary LEFT JOIN $detail_table on $summary.id={$detail_table}.{$summary}_id WHERE $summary.id={$params['id']}";
		$res = $this->db->query($sql);
		return $res->fetchAll();
	}
}

?>