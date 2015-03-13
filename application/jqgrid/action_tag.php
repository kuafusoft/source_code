<?php
require_once('action_jqgrid.php');

class action_tag extends action_jqgrid{
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
	
	protected function handlePost(){
        $pair = array();
        $pair['creater_id'] = $this->userInfo->id;
        $pair['name'] = $this->params['tag'];
        $pair['element_ids'] = $this->params['id'];
        $pair['public'] = $this->params['isPublic']=='checked'?1:0;
        $pair['db_table'] = $this->get('db').'.'.$this->get('table');
		$this->_tag($this->get('db'), $this->get('table'), $pair);
	}
	
    protected function _tag($db, $table, $pair){
// print_r($pair);	
	    $sql = "SELECT * FROM `{$db}`.`tag` WHERE `name`='{$pair['name']}' AND `db_table`='{$pair['db_table']}' AND creater_id={$pair['creater_id']}";
// print_r($sql);	    
	    $result = $this->db->query($sql);
//print_r($result);
	    if ($row = $result->fetch()){
			$old_id = explode(',', $row['element_ids']);
			$last = array_unique(array_merge($old_id, $pair['element_ids']));
// print_r($last);			
			$pair['element_ids'] = implode(',', $last);
			$this->db->update("$db.tag", $pair, "id=".$row['id']);
		}
		else{
			$pair['element_ids'] = implode(',', $pair['element_ids']);
			$this->db->insert("$db.tag", $pair);
		}
	    return;
    }	
}

?>