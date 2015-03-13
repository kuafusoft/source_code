<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class xt_codec_stream_action_information extends action_information{
	protected function paramsFor_view_edit($params){
		$view_params = parent::paramsFor_view_edit($params);
		return $view_params;
	}

	// protected function paramsFor_test_history($params){
		// $view_params = array('label'=>'Test History', 'id'=>$params['element'], 'disabled'=>empty($params['element']), 
			// 'view_file_dir'=>'xt/codec_stream');
		// return $view_params;
	// }
}

?>