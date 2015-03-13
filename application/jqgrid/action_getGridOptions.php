<?php
require_once('action_jqgrid.php');

class action_getGridOptions extends action_jqgrid{
	protected function handleGet(){
		$options = $this->getOptions();
		return json_encode($options);
	}
	
	protected function handlePost(){
		$options = $this->getOptions();
		return json_encode($options);
	}
}

?>