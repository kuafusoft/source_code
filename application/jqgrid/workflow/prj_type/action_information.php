<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class workflow_prj_type_action_information extends action_information{
	protected function getViewEditButtons($params){
		// 只有Admin有修改权限
		$view_buttons = array();
		if ($this->userAdmin->isAdmin($this->userInfo->id))
			$view_buttons = parent::getViewEditButtons($params);
		return $view_buttons;
	}
}

?>