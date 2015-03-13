<?php
require_once(APPLICATION_PATH.'/jqgrid/action_getGridOptions.php');

class xt_zzvw_cycle_detail_action_getGridOptions extends action_getGridOptions{
	protected function handlePost(){
		$options = $this->getOptions();
		return json_encode($options);
	}
}
?>