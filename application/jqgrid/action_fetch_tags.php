<?php
require_once('action_jqgrid.php');

class action_fetch_tags extends action_jqgrid{
	protected function handlePost(){
		return $this->table_desc->fetch_tags();
	}
}

?>