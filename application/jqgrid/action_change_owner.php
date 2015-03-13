<?php
require_once('action_jqgrid.php');

class action_change_owner extends action_jqgrid{
	protected function handlePost(){
		$ids = implode(',', json_decode($this->params['id']));
		$this->tool->update($this->get('table'), array('owner_id'=>$this->params['select_item']), "id in ($ids)", $this->get('db'));
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Owner';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$ownerList = $this->userAdmin->getUserList(array('active'=>1));
		foreach($ownerList as $id=>$name)
			$view_params['items'][$id] = compact('id', 'name');
		return $view_params;
	}
}

?>