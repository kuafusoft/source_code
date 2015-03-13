<?php
require_once('action_jqgrid.php');
require_once('exporterfactory.php');

class action_export extends action_jqgrid{
	protected $exporter = 'base';
	protected $exporter_dir = '/../library';
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "export_type.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		return $view_params;
	}
	
	protected function handlePost(){
	// print_r($this->params);
		$exporter = exporterFactory::get($this->params['export_type'], $this->params);
		$exporter->setOptions($this);
// print_r($exporter)		;
		return $exporter->export();
		// return parent::handlePost();
	}
	
}

?>