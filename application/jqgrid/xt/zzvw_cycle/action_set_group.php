<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_set_group extends action_jqgrid{

	public function handlePost(){
		$params = $this->parseParams();
		//$params['id'] = json_decode($params['id']);
print_r($params);
		$this->tool->update("cycle", array("group_id"=>$params['select_item']), "id in (".implode(",", $params['id']).")");
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Result';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$groups = $this->userAdmin->getGroups('');
		foreach($groups as $id=>$name){
			$view_params['items'][$id] = compact('id', 'name');
		}
		return $view_params;
	}
	
}

?>