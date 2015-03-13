<?php
require_once('action_jqgrid.php');

class action_inactivate extends action_jqgrid{
	protected function handlePost(){
		$this->tool->update($this->get('table'), array('isactive'=>ISACTIVE_INACTIVE), "id in (".implode(',', $this->params['id']).")", $this->get('db'));
	}
}

?>