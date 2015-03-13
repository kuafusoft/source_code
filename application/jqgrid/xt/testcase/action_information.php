<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class xt_testcase_action_information extends action_information{
	protected function init(&$controller){
		parent::init($controller);
	}
	
	public function setParams($params){
		parent::setParams($params);
		$this->params['ver_table'] = 'testcase_ver';
	}
	
	protected function paramsFor_view_edit($params){
		return parent::paramsFor_view_edit($params);
	}

	protected function paramsFor_edit_history($params){
		return parent::paramsFor_edit_history($params);
	}

	// protected function paramsFor_test_history($params){
		// $view_params = array('tab'=>'test_history', 'label'=>'Test History', 'id'=>$params['element'], 'disabled'=>empty($params['element']), 
			// 'db'=>'xt', 'table'=>'zzvw_cycle_detail', 'view_file_dir'=>'xt/testcase');
		// return $view_params;
	// }

	// protected function paramsFor_srs_cover($params){
		// $view_params = array('tab'=>'srs_cover', 'label'=>'SRS Cover', 'id'=>$params['element'], 'disabled'=>empty($params['element']), 
			// 'view_file_dir'=>'xt/testcase', 'db'=>'xt', 'table'=>'prj_srs_node_testcase');
		// return $view_params;
	// }

	protected function getViewEditButtons($params){
// print_r($params);
		$style = 'position:relative;float:right';
		$display = $style;
		$hide = $style.';display:none';
		$view_buttons = parent::getViewEditButtons($params);
		$vers = explode(',', $params['ver']);
		if (count($vers) > 1)
			return $view_buttons;
		$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id={$params['ver']}");
		$row = $res->fetch();
		$left_style = 'position:relative;float:left';
		$newBtns = array(
			'view_edit_coversrs'=>array('label'=>'Cover SRS', 'style'=>$left_style),
			'view_edit_abort'=>array('label'=>'Abort', 'style'=>$params['id'] ? $display:$hide),
		);
		switch($row['edit_status_id']){
			case EDIT_STATUS_PUBLISHED:
			case EDIT_STATUS_GOLDEN:
				$view_buttons['view_edit_coversrs'] = $newBtns['view_edit_coversrs'];
				unset($view_buttons['view_edit_abort']);
				break;
			default:
				$view_buttons['view_edit_abort'] = $newBtns['view_edit_abort'];
		}
// print_r($view_buttons);		
		return $view_buttons;
	}
}

?>