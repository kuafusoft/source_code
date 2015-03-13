<?php
require_once('action_jqgrid.php');

class action_checkUnique extends action_jqgrid{
	protected function handlePost(){
		$res = $this->tool->query("SELECT * FROM `{$this->table_name}` WHERE `{$this->params['field']}`=:value limit 0, 2", array('value'=>$this->params['value']));
		$rows = $res->rowCount();
		if ($rows == 1){
			$row = $res->fetch();
			if ($row['id'] == $this->params['id'])
				return 1;
			else
				return 2;
		}
		if ($rows == 0)
			return 1;
		return $rows;
	}
}

?>