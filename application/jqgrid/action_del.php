<?php
require_once('action_jqgrid.php');

class action_del extends action_jqgrid{
	protected function handlePost(){
		$this->tool->delete($this->get('table'), "id in (".$this->params['id'].")", $this->get('db'));
	}
}

?>