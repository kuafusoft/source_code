<?php
require_once(APPLICATION_PATH.'/jqgrid/action_export.php');

class xt_testcase_action_export extends action_export{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase';
		$prj = array(-1=>'==Lastest Published & Used Version==');
		$res = $this->tool->query("SELECT * FROM prj WHERE 1");
		while($row = $res->fetch()){
			$prj[$row['id']] = $row['name'];
		}
		$view_params['prj'] = $prj;
		return $view_params;
	}
}

?>