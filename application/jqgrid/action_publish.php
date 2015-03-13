<?php
require_once('action_jqgrid.php');

class action_publish extends action_jqgrid{
	protected function handlePost(){
		$table = $this->get('table');
        $params = $this->params;
		$params['ver'] = json_decode($params['ver']);
		$strEditStatus = EDIT_STATUS_EDITING.','.EDIT_STATUS_REVIEW_WAITING.','.EDIT_STATUS_REVIEWING.','.EDIT_STATUS_REVIEWED;
		$strPrj = '';
		$strIds = $params['ver'];
// print_r($strIds);
// return;		
		if (!empty($strIds)){
			//publish主要就是设置Version的状态为published，后续的事情由各模块自己完成
			$ver_db_table = $params['db'].'.'.$params['table'];
			$ver_op = $this->table_desc->getOptions();
			$node_ver_m2m = isset($ver_op['linkTables']['node_ver_m2m']) ? $ver_op['linkTables']['node_ver_m2m'] : array();
// print_r($node_ver_m2m);
			$sql = "SELECT * FROM $ver_db_table WHERE id IN ($strIds) and edit_status_id in ($strEditStatus)";
// print_r($sql);
// return;
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
				$this->tool->update($ver_db_table, 
					array(
						'edit_status_id'=>EDIT_STATUS_PUBLISHED, 
						'update_comment'=>"[{$this->userInfo->nickname} At ".date('Y-m-d H:i:s')." for Version {$row['ver']}]\n\r{$params['note']}\n\r{$row['update_comment']}"),
					"id={$row['id']}");
				$this->afterPublish($row, $params, $node_ver_m2m);
			}
		}
		return;
	}
	
	protected function afterPublish($ver, $params, $node_ver_m2m){
// print_r($node_ver_m2m);
// return;
		foreach($node_ver_m2m as $linkInfo){
			$db_table = $linkInfo['link_db'].'.'.$linkInfo['link_table'];
			$link_field = $linkInfo['link_field'];
			$self_link_field = $linkInfo['self_link_field'];
			if(empty($linkInfo['link_node_field']) || empty($linkInfo['node_field']))
				return;
			$node_field = $linkInfo['node_field'];
			$node_id = $ver[$node_field];
// print_r($db_table);			
			//删除原有连接
			//先获取该Version挂接的内容
			$links = array();
			$res = $this->tool->query("SELECT $link_field FROM $db_table where $self_link_field={$ver['id']}");
			while($row = $res->fetch()){
				$links[] = $row[$link_field];
			}
			$strLinks = implode(',', $links);
			//删除挂接在其他publish的Version上的重叠连接
			$this->removeLink($node_id, $ver['id'], $db_table, $strLinks, $linkInfo);
		}
	}
	
	protected function removeLink($node_id, $ver_id, $db_table, $strLinks, $linkInfo){
		$link_node_field = $linkInfo['link_node_field'];
		$self_link_field = $linkInfo['self_link_field'];
		$link_field = $linkInfo['link_field'];
		$ver_db_table = $this->get('db').'.'.$this->get('table');
		
		$this->tool->query("DELETE $db_table FROM $db_table LEFT JOIN $ver_db_table ON $db_table.$self_link_field=$ver_db_table.id ".
			" WHERE $db_table.$link_node_field=$node_id AND $db_table.$self_link_field!=$ver_id AND $db_table.$link_field IN ($strLinks) ".
			" AND $ver_db_table.edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "publish.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		return $view_params;
	}

}

?>