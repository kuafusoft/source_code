<?php
require_once('action_jqgrid.php');

class action_his_diff extends action_jqgrid{
	protected function getViewParams($params){
// print_r($params);	
		$view_params = $params;
		$view_params['view_file'] = "ver_diff.phtml";
		$view_params['view_file_dir'] = '/jqgrid';
		
		$list_params = array('db'=>$params['db'], 'table'=>$params['table']);
		$action_list = actionFactory::get($this->controller, 'list', $list_params);
		foreach($params['id'] as $id){
			$list_params['id'] = $id;
			$action_list->setParams($list_params);
			$ret = $action_list->handlePost();
			$view_params['vers'][$ret['rows'][0]['id']] = $ret['rows'][0];
		}

		$node = tableDescFactory::get($params['db'], $params['table'], array());
		$options = $node->getOptions();
		$view_params['field_options'] = $options['edit'];
		
		return $view_params;
	}
}

?>