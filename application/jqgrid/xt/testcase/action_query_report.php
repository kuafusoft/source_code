<?php
require_once('action_jqgrid.php');
require_once('exporterfactory.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class xt_testcase_action_query_report extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = 'query_report.phtml';
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase';
		return $view_params;
	}
	
	protected function handlePost(){
		// print_r($this->params);
		$exporter = exporterFactory::get('query_report', $this->params);
		$exporter->setOptions($this);
		return $exporter->export();
	}
	
	

}
?>