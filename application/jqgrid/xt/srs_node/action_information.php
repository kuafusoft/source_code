<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_version_information.php');

class xt_srs_node_action_information extends action_version_information{
	protected function init(&$controller){
		parent::init($controller);
		$this->params['ver_table'] = 'srs_node_ver';
	}
	
	protected function paramsFor_view_edit($params){
		$view_params = parent::paramsFor_view_edit($params);
		if (empty($params['id'])){
			if (!empty($params['parent'])) // 为code设置默认值
				$view_params['node']['value']['code'] = $this->table_desc->getNextCode($params['parent']);
			else
				$view_params['node']['value']['code'] = $this->table_desc->getNextCode(0);
		}
		return $view_params;
	}

	protected function paramsFor_edit_history($params){
		return parent::paramsFor_edit_history($params);
	}

	protected function paramsFor_test_history($params){
		// get the test history
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE testcase_id=".$params['id']);
		$test_history = $res->fetchAll();
		$view_params = array('label'=>'Test History', 'id'=>$params['element'], 'disabled'=>empty($params['element']),
			'dir'=>'xt/testcase', 'test_history'=>$test_history);
		return $view_params;
	}

	protected function getViewEditButtons($params){
		$view_buttons = parent::getViewEditButtons($params);
		$vers = explode(',', $params['ver']);
		if (count($vers) > 1)
			return $view_buttons;
		$style = 'position:relative;float:left';
		$newBtns = array(
			'ask2review'=>array('onclick'=>'xt.srs_node.ask2review('.$params['ver'].')', 'label'=>'Ask To Review', 'style'=>$style),
			'publish'=>array('onclick'=>'xt.srs_node.publish('.$params['ver'].')', 'label'=>'Publish', 'style'=>$style),
		);
		$res = $this->db->query("select * from srs_node_ver WHERE id=".$params['ver']);
		$row = $res->fetch();
		switch($row['edit_status_id']){
			case EDIT_STATUS_EDITING:
				$view_buttons['ask2review'] = $newBtns['ask2review'];
				$view_buttons['publish'] = $newBtns['publish'];
				break;
			case EDIT_STATUS_REVIEW_WAITING:
			case EDIT_STATUS_REVIEWING:
			case EDIT_STATUS_REVIEWED:
				$view_buttons['publish'] = $newBtns['publish'];
				break;
		}
		return $view_buttons;
	}
}

?>