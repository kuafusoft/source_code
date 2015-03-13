<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_set_crid extends xt_zzvw_cycle_detail_action_jqgrid{

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
		$res = $this->tool->query("SELECT id, tester_id, defect_ids FROM cycle_detail WHERE id in (".implode(',', $element).")");
		$i = 0;
		while($row = $res->fetch()){
			if($isAdmin || $info['creater_id'] == $currentUser || $info['assistant_owner_id'] == $currentUser || $row['tester_id'] == $currentUser){
				$i ++;
				$data = array();
				if(empty($row['defect_ids'])){
					$data['defect_ids'] = $params['defect_ids'];
				}
				else{
					$keys = explode(";", $row['defect_ids']);
					if(in_array($params['defect_ids'], $keys))
						$data['defect_ids'] = $row['defect_ids'];
					else
						$data['defect_ids'] = $row['defect_ids'].";".$params['defect_ids'];
				}
				$this->tool->update('cycle_detail', array('defect_ids'=>$data['defect_ids'], 'jira_key_ids'=>$data['defect_ids']), "id=".$row['id']);					
			}
		}
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Comment';
		$view_params['view_file'] = 'newElement.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		$view_params['cols'] = array(
			array('id'=>'defect_ids', 'name'=>'defect_ids', 'label'=>'CRID', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
		);
		return $view_params;
	}
	
}

?>