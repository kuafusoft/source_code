<?php
require_once('action_jqgrid.php');

class xt_testcase_ver_action_add2cycle extends action_jqgrid{
	protected function handlePost(){
		
//		$this->db->update($this->get('cycle_detail'), array('cycle_id'=>$this->params['select_item']), "id in ({$this->params['id']})");
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Cycle';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$res = $this->tool->query("SELECT id, name FROM cycle WHERE cycle_status_id=1 and isactive=1");
		while($row = $res->fetch()){
			$view_params['items'][$row['id']] = $row;
		}
		return $view_params;
	}
}

?>