<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class workflow_prj_trace_action_information extends action_information{
	// protected function detailViewParams($id, $detail_table){
		// $table = $this->get('table');
		// $params = array('db'=>$this->get('db'));
		// $detail_table_desc = tableDescFactory::get($this->get('db'), $detail_table);
// //print_r("SELECT * FROM $detail_table WHERE {$table}_id=$id']}");
		// $res = $this->db->query("SELECT * FROM $detail_table WHERE {$table}_id=$id");
		// if ($row = $res->fetch())
			// $params['id'] = $row['id'];
		// else
			// $params['id'] = 0;
// //print_r($params);					
		// $detail = $detail_table_desc->paramsForViewEdit($params);
// // print_r($detail);		
		// if ($params['id'] == 0){
			// if (!empty($this->params['prj_id'])){
				// $detail['value']['prj_id'] = $this->params['prj_id'];
				// $res = $this->db->query("select progress from prj where id=".$this->params['prj_id']);
				// if ($row = $res->fetch())
					// $detail['value']['progress'] = $row['progress'];
			// }
		// }
		// return $detail;
	// }
}

?>