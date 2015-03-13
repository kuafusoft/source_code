<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_tag_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		if (!$this->userAdmin->isAdmin($this->userInfo->id)){
			$params['searchConditions'][] = array('field'=>'creater_id', 'op'=>'=', 'value'=>$this->userInfo->id);
		}
		
		return $params;
	}
}

?>