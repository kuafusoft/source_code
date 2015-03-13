<?php
require_once(APPLICATION_PATH.'/jqgrid/action_index.php');

class xt_zzvw_cycle_detail_action_index extends action_index{
	protected function handlePost(){
		$this->controller->view->options = $this->getOptions();
	}
}
?>