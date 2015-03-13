<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_update_information_page.php');

class xt_testcase_action_update_information_page extends action_update_information_page{
	protected function init(&$controller){
		parent::init($controller);
		$this->params['ver_table'] = 'testcase_ver';
	}
	
	protected function getViewEditButtons($params){
		$view_buttons = parent::getViewEditButtons($params);
		$vers = explode(',', $params['ver']);
		if (count($vers) > 1)
			return $view_buttons;
		$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id={$params['ver']}");
		$row = $res->fetch();
		$testcase_ids = $row['testcase_id'];
		$style = 'position:relative;float:left';
		$newBtns = array(
			'view_edit_ask2review'=>array('label'=>'Ask To Review', 'style'=>$style),
			'view_edit_publish'=>array('label'=>'Publish', 'style'=>$style),
			'view_edit_coversrs'=>array('label'=>'Cover SRS', 'style'=>$style),
		);
		switch($row['edit_status_id']){
			case EDIT_STATUS_EDITING:
				$view_buttons['view_edit_ask2review'] = $newBtns['view_edit_ask2review'];
				$view_buttons['view_edit_publish'] = $newBtns['view_edit_publish'];
				break;
			case EDIT_STATUS_REVIEW_WAITING:
			case EDIT_STATUS_REVIEWING:
			case EDIT_STATUS_REVIEWED:
				$view_buttons['view_edit_publish'] = $newBtns['view_edit_publish'];
				break;
			case EDIT_STATUS_PUBLISHED:
			case EDIT_STATUS_GOLDEN:
				$view_buttons['view_edit_coversrs'] = $newBtns['view_edit_coversrs'];
				break;
		}
		return $view_buttons;
	}
}

?>