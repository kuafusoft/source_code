<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class action_node_detail_information extends action_information{
	protected function paramsFor_view_edit($params){
// print_r($params);
// print_r($this->params);
		$table = $this->get('table');
		$type_table = $table.'_type';
		$type_field = $type_table.'_id';
		$view_params = parent::paramsFor_view_edit($params);
		if (empty($params['id']) && !empty($params[$type_field])){
//print_r($view_params);		
			$view_params['node']['value'][$type_field] = $params[$type_field];
			$view_params['node']['model'][$type_field]['editable'] = false;
// print_r($view_params['node']['model'][$type_field]);
		}
//print_r($this->params);		
		$view_params['detail'] = $this->getDetailParams($params, $view_params['node']['value']);
		$view_params['view_file'] = 'node_detail_view_edit.phtml';
		$view_params['view_file_dir'] = '';
		return $view_params;
	}
	
	protected function getDetailParams($params, $nodeValue){
//print_r($params);
		$detail = array();
		$table = $this->get('table');
		$type_table = $table.'_type';
		$type_field = $type_table.'_id';
// print_r($nodeValue);		
		$type_id = !empty($nodeValue[$type_field]) ? $nodeValue[$type_field] : 1;
		$id = empty($nodeValue['id']) ? 0 : $nodeValue['id'];
		$detail = $this->getTypeTableParams($type_id, $id);
		return $detail;
	}
	
	protected function getTypeTableParams($type_id, $id){
		$detail = array();
		$table = $this->get('table');
		$type_table = $table.'_type';
		$type_field = $type_table.'_id';
		$res = $this->db->query("SELECT * FROM $type_table where id=$type_id");
		if ($row = $res->fetch()){
			$detail_table = $row['table_name'];
			if (!empty($detail_table)){
				$detail = $this->detailViewParams($id, $detail_table);
			}
		}
//print_r($detail);
		return $detail;
	}
	
	protected function detailViewParams($id, $detail_table){
		$table = $this->get('table');
		$params = array('db'=>$this->get('db'), 'table'=>$detail_table);
		// $detail_table_desc = tableDescFactory::get($this->get('db'), $detail_table);
// print_r("SELECT * FROM $detail_table WHERE {$table}_id=$id']");
		$res = $this->db->query("SELECT * FROM $detail_table WHERE {$table}_id=$id");
		if ($row = $res->fetch())
			$params['id'] = $row['id'];
		else
			$params['id'] = 0;
		$action_information = actionFactory::get(null, 'information', $params);
// print_r($params);		
		$detail = $action_information->paramsFor_view_edit($params);
		// $detail = $detail_table_desc->paramsForViewEdit($params);
		return $detail;
	}
}
?>