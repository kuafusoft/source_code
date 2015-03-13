<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_version_information.php');

class qygl_zzvw_hb_yg_action_information extends action_information{
	protected function init(&$controller){
		parent::init($controller);
	}
	
	protected function paramsFor_view_edit($params){
		return parent::paramsFor_view_edit($params);
	}

	protected function paramsFor_scdj_history($params){ //过往生产情况
		// get the test history
		// $res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE testcase_id=".$params['id']);
		// $test_history = $res->fetchAll();
		$view_params = array('label'=>'生产情况', 'id'=>$params['element'], 'disabled'=>empty($params['element']), 
			'view_file_dir'=>'qygl/zzvw_hb_yg');
		return $view_params;
	}

	protected function paramsFor_gz_history($params){ // 工资情况
		$view_params = array('label'=>'工资情况', 'id'=>$params['element'], 'disabled'=>empty($params['element']), 
			'view_file_dir'=>'qygl/zzvw_hb_yg');
		return $view_params;
	}

}

?>