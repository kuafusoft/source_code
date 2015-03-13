<?php
require_once('action_jqgrid.php');//APPLICATION_PATH.'/jqgrid/action_ver_diff.php');
require_once(APPLICATION_PATH.'/jqgrid/mcu/zzvw_device_ver/diff.php');

class mcu_zzvw_device_ver_action_diff extends action_jqgrid{
	protected function getViewParams($params){
// print_r($params);	
		$view_params = $params;
		$view_params['view_file'] = "ver_diff.phtml";
		$view_params['view_file_dir'] = '/jqgrid/mcu/zzvw_device_ver';
		$ret = tree_diff($params, $this->tool);
		$view_params['levels'] = $ret['levels'];
		$view_params['data'] = $ret['data'];
		return $view_params;
	}
}
?>