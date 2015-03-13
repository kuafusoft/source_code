<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_saveOneResult.php');

class xt_zzvw_cycle_detail_stream_action_saveOneResult extends xt_zzvw_cycle_detail_action_saveOneResult{
	protected function returnData($data){
		$data['codec_stream_result'] = 'All Blank';
		$res = $this->tool->query("select id, name from result_type where id=".$data['result_type_id']);
		if($info = $res->fetch())
			$data['codec_stream_result'] = 'All '.$info['name'];
		return json_encode($data);
	}
}

?>