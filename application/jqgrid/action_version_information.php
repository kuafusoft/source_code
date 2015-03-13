<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_information.php');

class action_version_information extends action_information{
	protected function paramsFor_view_edit($params){
// print_r("ver_inforation ");	
		$view_params = parent::paramsFor_view_edit($params);
// print_r($params);		
		if (empty($params['ver']))
			$vers = array();
		else
			$vers = explode(',', $params['ver']);
		if (count($vers) > 1){ // 先显示Edit_history页，选择一个Version后进入
			$view_params['disabled'] = true;
		}
		else
			$view_params['ver'] = $this->getVerParams($params);
		$view_params['view_file'] = 'node_ver_view_edit.phtml';
		$view_params['view_file_dir'] = '';
//print_r($view_params['ver']);
		return $view_params;
	}
	
	protected function getNodeParamsForViewEdit($params){
		$node = parent::getNodeParamsForViewEdit($params);
		// $res = $this->tool->query("SELECT COUNT(*) as cc FROM {$params['ver_table']} WHERE {$params['table']}_id={$params['id']}");
		// $row = $res->fetch();
		// $node['totalVers'] = $row['cc'];
		return $node;
	}
	
	protected function getVerParams($params){
		$ver_table_desc = tableDescFactory::get($params['db'], $params['ver_table']);
		$case_id = $params['id'];
		$params['id'] = isset($params['ver']) ? $params['ver'] : 0;
		$ver = $ver_table_desc->paramsForViewEdit($params);
		$ver['editing'] = empty($case_id);
		return $ver;
	}
	protected function paramsFor_edit_history($params){
		$view_params = array('label'=>'Edit History', 'db'=>$this->get('db'), 'table'=>$this->get('ver_table'), 'id'=>$params['id'], 'disabled'=>empty($params['id']), 'dir'=>'');
		return $view_params;
	}

	protected function getViewEditButtons($params){
		$view_buttons = parent::getViewEditButtons($params);
		
		$style = 'position:relative;float:right';
		$display = $style;
		$hide = $style.';display:none';
		
		if(!empty($params['id'])){
			$published = false;
			// 检测当前version是否published
			$ver = explode(',', $params['ver']);
			
			if (count($ver) == 1){
				$res = $this->tool->query("SELECT edit_status_id FROM {$params['ver_table']} where id={$params['ver']}");
				$row = $res->fetch();
				if ($row['edit_status_id'] == EDIT_STATUS_PUBLISHED || $row['edit_status_id'] == EDIT_STATUS_GOLDEN)
					$published = true;
			}
			$view_buttons['view_edit_abort'] = array('label'=>'Abort', 'style'=>$published ? $hide:$display);
		}
		return $view_buttons;
	}		
}

?>