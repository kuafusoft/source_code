<?php
require_once('action_jqgrid.php');

class action_ver_diff extends action_jqgrid{
	protected function getViewParams($params){
// print_r($params);	
		$view_params = $params;
		$view_params['view_file'] = "ver_diff.phtml";
		$view_params['view_file_dir'] = '/jqgrid';
		
		$node_db = $params['db'];
		$node_table = substr($params['table'], 0, -4);
		$node = tableDescFactory::get($node_db, $node_table, array());
		$options = $node->getOptions();
		$node_edit_fields = $options['edit'];
		
		$ver = tableDescFactory::get($params['db'], $params['table'], array());
		$ver_options = $ver->getOptions();
		$ver_edit_fields = $ver_options['edit'];

		$res = $this->tool->query("SELECT * FROM {$params['db']}.{$params['table']} WHERE id={$params['id'][0]}");
		$ver = $res->fetch();
		$node_id = $ver[$node_table.'_id'];
		
		$list_params = array('db'=>$node_db, 'table'=>$node_table, 'id'=>$node_id);
		$action_list = actionFactory::get($this->controller, 'list', $list_params);
		$action_list->setParams($list_params);
		$ret = $action_list->handlePost();
		$node_value = $ret['rows'][0];
		
		$ver_list_params = array('db'=>$params['db'], 'table'=>$params['table']);
		$ver_action_list = actionFactory::get($this->controller, 'list', $ver_list_params);
		$ver_value = array();
		foreach($params['id'] as $id){
			$ver_list_params['id'] = $id;
			$ver_action_list->setParams($ver_list_params);
			$ret = $ver_action_list->handlePost();
			$ver_value['Version '.$ret['rows'][0]['ver']] = array_merge($node_value, $ret['rows'][0]);
		}
		$view_params['vers'] = $ver_value;
		$view_params['field_options'] = array_merge($node_edit_fields, $ver_edit_fields);
// print_r($view_params);		
		return $view_params;
	}
}

?>