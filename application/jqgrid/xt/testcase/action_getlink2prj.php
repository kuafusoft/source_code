<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_link2prj.php');

class xt_testcase_action_getlink2prj extends action_ver_link2prj{
	protected function handlePost(){
		return $this->handleGet();
	}

};


?>