<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_set_build_result extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$element = $this->caclIDs($params);
		if($element == "error")
			return "error";
		$sql = "SELECT creater_id, assistant_owner_id FROM cycle WHERE id=".$params['parent'];
		$res = $this->tool->query($sql);
		$info = $res->fetch();	
		$currentUser = $this->userInfo->id;
		$isAdmin = false;
		if(!empty($this->userInfo->isAdmin))
			$isAdmin = true;
		$res = $this->tool->query("SELECT id, tester_id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch()){
			if($isAdmin || $info['creater_id'] == $currentUser || $info['assistant_owner_id'] == $currentUser || $row['tester_id'] == $currentUser)
				$ret = $this->tool->update('cycle_detail', array('build_result_id'=>$params['select_item']), 'id='.$row['id']);
		}
		if(!empty($ret) && count($element == 1)){
			$res = $this->tool->query("SELECT id, build_result_id FROM cycle_detail WHERE id=".$element[0]);
			$data = $res->fetch();
			return json_encode($data);
		}
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Build Result';
		$view_params['view_file'] = 'select_item.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$res = $this->tool->query("select id, name from result_type");
		while($row = $res->fetch()){
			$id = $row['id'];
			$name = $row['name'];
			$view_params['items'][$id] = compact('id', 'name');
		}
		return $view_params;
	}
}

?>