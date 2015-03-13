<?php
require_once('action_jqgrid.php');

class xt_srs_node_action_getNextCode extends action_jqgrid{
	protected function handlePost(){
		return $this->table_desc->getNextCode($this->params['srs_module_id']);
	}
}

?>