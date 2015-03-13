<?php
require_once('action_jqgrid.php');

class action_index extends action_jqgrid{
	protected function getViewParams($params){
		$ret = $this->getOptions();
		return $ret;
	}
	
	protected function _execute(){
		$ret = $this->getOptions();
		return $ret;
		// $this->controller->view->options = $this->getOptions();
	}
}

?>