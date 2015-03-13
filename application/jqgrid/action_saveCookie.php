<?php
require_once('action_jqgrid.php');

class action_saveCookie extends action_jqgrid{
	protected function handlePost(){
		return $this->saveCookie($this->params);
		
        $this->params['name'] = $this->params['db'].'_'.$this->params['table'];
        return $this->userAdmin->saveCookie($this->params);
	}
}

?>