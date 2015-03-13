<?php
require_once(APPLICATION_PATH.'/jqgrid/action_export.php');
require_once('exporterfactory.php');

class xt_srs_node_action_export extends action_export{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/xt/srs_node';
		$prj = array();
		$res = $this->db->query("SELECT * FROM prj WHERE 1");
		while($row = $res->fetch()){
			$prj[$row['id']] = $row['name'];
		}
		$view_params['prj'] = $prj;
		return $view_params;
	}
}

?>