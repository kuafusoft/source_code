<?php
require_once('action_jqgrid.php');

class action_subscribe extends action_jqgrid{
	protected function handlePost(){
        $params = $this->params;
		$params['id'] = implode(',', json_decode($params['id']));
        $dbTable = "`{$this->get('db')}`.`{$this->get('table')}`";
        $displayField = $this->table_desc->getDisplayField();
		$caption = $this->table_desc->getCaption();
        $result = $this->db->query("select * from $dbTable WHERE id in ({$params['id']})");
		$items = array();
        while($row = $result->fetch()){
			$items[$row['id']] = $description = '<strong>'.$caption.':'.$row[$displayField].'</strong>';
		}
        $this->userAdmin->subscribe($this->userInfo->id, $dbTable, $items);
	}
}

?>