<?php
require_once('action_jqgrid.php');
require_once('importerfactory.php');

class action_import extends action_jqgrid{
	protected $importer = 'base';
	protected $importer_dir = '/../library';
	protected function init(&$controller){
		parent::init($controller);
	}
	
	protected function getViewParams($params){
		$view_params = $params;
	
		$view_params['view_file'] = "import_type.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		return $view_params;
	}
	
	protected function handlePost(){
print_r($this->params);		
print_r($_FILES);
		$importer = importerFactory::get($this->params['import_type'], $this->params);
		$importer->setOptions($this);
		return $importer->import();
	}
	
}

?>