<?php
require_once('action_jqgrid.php');

class action_removeFromTag extends action_jqgrid{
	protected function getViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = $params;
		$view_params['type'] = 'Tag';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$pair['creater_id'] = $this->userInfo->id;
		$pair['db_table'] = $db.'.'.$table;
		$sql = "SELECT id, name FROM `{$db}`.`tag` WHERE `db_table`='{$pair['db_table']}' AND creater_id={$pair['creater_id']}";
		$res = $this->db->query($sql);
		$id = 0;
		$name = '';
		$view_params['items'][$id] = compact('id', 'name');
		while($row = $res->fetch()){
			$id = $row['id'];
			$name = $row['name'];
			$view_params['items'][$id] = compact('id', 'name');
		}
		return $view_params;
	}
	
	protected function handlePost(){
        $params = $this->parseParams();
		$params['id'] = json_decode($params['id']);
print_r($params);
		$db = $this->get('db');
		$table = $this->get('table');
		$creater_id = $this->userInfo->id;
		$db_table = $db.'.'.$table;
		$sql = "SELECT id, element_ids FROM `{$db}`.`tag` WHERE `db_table`='{$db_table}' AND creater_id={$creater_id} and id = {$params['select_item']}";
		$res = $this->db->query($sql);
		if($row = $res->fetch()){
			$element_ids = explode(",", $row['element_ids']);
			foreach($element_ids as $k=>$v){
				if(in_array($v, $params['id']))
					unset($element_ids[$k]);
			}
			$this->db->update('tag', array("element_ids"=>implode(",", $element_ids)), "id=".$row['id']);
		}
	}
	
    // protected function _tag($db, $table, $pair){
// // print_r($pair);	
	    // $sql = "SELECT * FROM `{$db}`.`tag` WHERE `name`='{$pair['name']}' AND `db_table`='{$pair['db_table']}' AND creater_id={$pair['creater_id']}";
// // print_r($sql);	    
	    // $result = $this->db->query($sql);
// //print_r($result);
	    // if ($row = $result->fetch()){
			// $old_id = explode(',', $row['element_ids']);
			// $last = array_unique(array_merge($old_id, $pair['element_ids']));
// // print_r($last);			
			// $pair['element_ids'] = implode(',', $last);
			// $this->db->update("$db.tag", $pair, "id=".$row['id']);
		// }
		// else{
			// $pair['element_ids'] = implode(',', $pair['element_ids']);
			// $this->db->insert("$db.tag", $pair);
		// }
	    // return;
    // }	
}

?>