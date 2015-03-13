<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_node_detail_information.php');

class workflow_daily_note_action_information extends action_node_detail_information{
	protected function parseParams(){
		parent::parseParams();
	// print_r($this->params);
		if(!empty($this->params['parent'])){
			//设置filter
			$rules = array();
			$rules[] = array('field'=>'prj_id', 'op'=>'eq', 'data'=>$this->params['parent']);
			$filter = array('groupOp'=>'AND', 'rules'=>$rules);
			$this->params['filter'] = $filter;
		}
// print_r("params : ");			
// print_r($this->params);			
		return $this->params;
	}
	
	protected function detailViewParams($id, $detail_table){
		$table = $this->get('table');
		$params = array('db'=>$this->get('db'));
		$detail_table_desc = tableDescFactory::get($this->get('db'), $detail_table);
//print_r("SELECT * FROM $detail_table WHERE {$table}_id=$id']}");
		$res = $this->db->query("SELECT * FROM $detail_table WHERE {$table}_id=$id");
		if ($row = $res->fetch())
			$params['id'] = $row['id'];
		else
			$params['id'] = 0;
//print_r($params);					
		$detail = $detail_table_desc->paramsForViewEdit($params);
// print_r($detail);		
		if ($params['id'] == 0){
			if (!empty($this->params['prj_id'])){
				$detail['model']['prj_id']['editable'] = false;
				$detail['value']['prj_id'] = $this->params['prj_id'];
				$res = $this->db->query("select progress from prj where id=".$this->params['prj_id']);
				if ($row = $res->fetch())
					$detail['value']['progress'] = $row['progress'];
			}
		}
		return $detail;
	}
	
	// protected function getViewEditButtons($params){
		// $view_buttons = parent::getViewEditButtons($params);

		// $style = 'position:relative;float:left';
		// $display = $style;
		// $hide = $style.';display:none';
		
		// $view_buttons['import_content'] = array('label'=>'Import Content', 'style'=>$params['id'] ? $hide:$display);
		// return $view_buttons;
	// }			
}

?>