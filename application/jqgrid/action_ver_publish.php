<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_action.php');

class action_ver_publish extends action_ver_action{
	protected function handlePost(){
		$table = $this->get('table');
        $params = $this->params;
		// $params['id'] = json_decode($params['id']);
		if (empty($params['from']))$params['from'] = $table;
		$strEditStatus = EDIT_STATUS_EDITING.','.EDIT_STATUS_REVIEW_WAITING.','.EDIT_STATUS_REVIEWING.','.EDIT_STATUS_REVIEWED;
		$strPrj = '';
		$strIds = implode(',', $params['id']);
		if($params['from'] == $table){
			$res = $this->tool->query("SELECT {$this->ver_table}_id as ver_id FROM {$this->link_table} WHERE {$table}_id in ($strIds) AND {$this->link_field}={$params[$this->link_field]}s AND edit_status_id in ($strEditStatus)");
			while($row = $res->fetch())
				$ver[] = $row['ver_id'];
			if (!empty($ver))
				$strIds = implode(',', $ver);
			else
				$strIds = '';
		}
		if (!empty($strIds)){
			//检查对应的Version连接的projects，将这些link转移到新的version
			$sql = "SELECT group_concat(DISTINCT {$this->link_field}) as link_ids, {$table}_id as t_id, {$this->ver_table}_id as ver_id, edit_status_id ".
				" FROM {$this->link_table} WHERE {$this->ver_table}_id IN ($strIds) group by {$this->ver_table}_id";
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
				if ($row['edit_status_id'] == EDIT_STATUS_PUBLISHED || $row['edit_status_id'] == EDIT_STATUS_GOLDEN)
					continue;
				//将要被删除的记录加入history表
				$where = "{$this->link_field} IN ({$row['link_ids']}) and {$table}_id={$row['t_id']} and (edit_status_id=".EDIT_STATUS_PUBLISHED." OR edit_status_id=".EDIT_STATUS_GOLDEN.")";
				$sql = "INSERT INTO {$this->history_table} (act, {$this->link_field}, {$table}_id, {$this->ver_table}_id) ".
					" SELECT 'remove', {$this->link_field}, {$table}_id, {$this->ver_table}_id".
					" FROM {$this->link_table}".
					" WHERE $where";
				$this->tool->query($sql);
//print_r($sql."\n");				
				$this->tool->delete($this->link_table, $where, $this->get('db'));
			}
			$sql = "INSERT INTO {$this->history_table} (act, {$this->link_field}, {$table}_id, {$this->ver_table}_id) ".
				" SELECT 'add', {$this->link_field}, {$table}_id, {$this->ver_table}_id".
				" FROM {$this->link_table} link".
				" WHERE link.{$this->ver_table}_id in ($strIds) AND link.edit_status_id IN ($strEditStatus)";
			$this->tool->query($sql);
//print_r($sql."\n");
			$where = "ver.id in ($strIds) AND ver.edit_status_id IN ($strEditStatus) AND link.{$this->ver_table}_id=ver.id";
			$sql = 'UPDATE '.$this->ver_table.' ver, '.$this->link_table.' link SET ver.edit_status_id='.EDIT_STATUS_PUBLISHED.
				', ver.update_comment=concat(update_comment, "\n\r['.$this->userInfo->nickname.' At '.date('Y-m-d H:i:s').']\n\r", :note)'.
				', link.edit_status_id='.EDIT_STATUS_PUBLISHED.
				' WHERE '.$where;
//print_r($sql."\n");				
			$this->tool->query($sql, array('note'=>$params['note']));
		}
		return;
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "publish.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		return $view_params;
	}

}

?>