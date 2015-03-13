<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_set_comment extends xt_zzvw_cycle_detail_action_jqgrid{

	public function handlePost(){
		//$params = $this->parseParams();	
		$params = $this->params;
		$element = $this->caclIDs($params);
		if($element == "error")
			return "error";
		$date = date("Y-m-d H:i:s");
		$author = $this->userInfo->nickname;
		$currentUser = $this->userInfo->id;
		$isAdmin = false;
		if(!empty($this->userInfo->isAdmin))
			$isAdmin = true;
		$sql = "SELECT creater_id, assistant_owner_id FROM cycle WHERE id=".$params['parent'];
		$res = $this->tool->query($sql);
		$info = $res->fetch();
		if(!empty($params['comment']))
			$params['comment'] = $author.":".$params['comment'];
		$res = $this->tool->query("SELECT id, tester_id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch()){
			if($isAdmin || $info['creater_id'] == $currentUser || $info['assistant_owner_id'] == $currentUser || $row['tester_id'] == $currentUser){
				$this->tool->update('cycle_detail', array('result_type_id'=>$params['result_type_id'], 'comment'=>mysql_real_escape_string($params['comment']), 'defect_ids'=>$params['defect_ids'], 'finish_time'=>date("Y-m-d H:i:s")), "id=".$row['id']);		
				$this->updatelastresult($row['id'], $params['result_type_id']);
			}
		}
		// $params['id'] = json_decode($params['id']);
		// $data = array('result_type_id'=>$params['select_item'], 'finish_time'=>$date, "id=".$params['id'][0]);
		// return $this->returnData($data);
	}
	
	// protected function returnData($data){
		// return json_encode($data);
	// }
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Comment';
		$view_params['view_file'] = 'newElement.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$view_params['cols'] = array(
			array('id'=>'result_type_id', 'name'=>'result_type_id', 'label'=>'Result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>2,'editoptions'=>array('value'=>array('2'=>'Fail')), 'editrules'=>array('required'=>true)),
			array('id'=>'comment', 'name'=>'comment', 'label'=>'CR Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
			array('id'=>'defect_ids', 'name'=>'defect_ids', 'label'=>'CRID', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
		);
		return $view_params;
	}
	
}

?>