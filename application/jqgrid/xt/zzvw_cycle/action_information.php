<?php
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class xt_zzvw_cycle_action_information extends action_information{
	protected function buttons($oper){
		$params = $this->parseParams();
		//$params['id'] = json_decode($params['id']);
		$view_params['id'] = isset($params['id']) ? $params['id'] : (isset($params['id']) ? $params['id'] : 0);
		if (is_array($view_params['id']))
			$view_params['id'] = implode(',', $view_params['id']);
		$view_buttons = $this->getViewEditButtons($view_params);
		if($oper == 'freeze')
			unset($view_buttons['view_edit_edit']);
// print_r($view_buttons);
		$view_params = array('btn'=>$view_buttons, 'editable'=>true);
		$this->renderView('button_edit.phtml', $view_params);
	}
	
	protected function getViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getViewParams($params);
		if (!empty($params['id'])){
			$view_params['tabs']['cycle_detail'] = array('view_file_dir'=>'xt/zzvw_cycle', 'label'=>'Cycle Cases', 'disabled'=>!$params['id'], 'id'=>$params['id']);
			$res = $this->tool->query("select group_id from cycle where id=".$params['id']);
			if($info = $res->fetch()){
				if($info['group_id'] == 3 || $info['group_id'] == 9)// && $info['testcase_type_id'] == 2)
					$view_params['tabs']['cycle_stream'] = array('view_file_dir'=>'xt/zzvw_cycle', 'label'=>'Cycle Streams', 'disabled'=>!$params['id']);
			}
		}	
		return $view_params;
	}
	
	protected function getViewEditButtons($params){
		$btns = parent::getViewEditButtons($params);
		unset($btns['view_edit_saveandnew']);
		if (!empty($params['id'])){
			$roleAndStatus = $this->table_desc->roleAndStatus('cycle', $params['id'], 0, array('status'=>'cycle_status_id', 'assistant_owner'=>'assistant_owner_id'));
// print_r($roleAndStatus);
			$role = $roleAndStatus['role'];
			$status = $roleAndStatus['status'];
			$style = 'position:relative;float:left';
			$display = $style;
			$hide = $style.';display:none';	
			$newBtns = array(
				'unfreeze'=>array('label'=>'unFreeze', 'title'=>'Unfreeze This Cycle', 'style'=>($status == CYCLE_STATUS_ONGOING) ? $hide : $style),
				'inside_freeze'=>array('label'=>'Freeze', 'title'=>'Freeze This Cycle', 'style'=>($status == CYCLE_STATUS_ONGOING) ? $style : $hide),
				'uploadfile' => array('label'=>'Upload', 'title'=>'Import File to Cycle', 'style'=>($status == CYCLE_STATUS_ONGOING) ? $style : $hide),
				'view_edit_export' => array('style'=>$style, 'label'=>'Export')
			);
			$btns = array_merge($btns, $newBtns);
			if($status == CYCLE_STATUS_FROZEN)
				unset($btns['view_edit_edit']);
		}
		return $btns;
	}
}
?>