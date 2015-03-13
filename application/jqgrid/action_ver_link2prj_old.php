<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_action.php');

class action_ver_link2prj extends action_ver_action{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "link2prj.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		$projects = array();
		$res = $this->tool->query("SELECT prj.id, prj.name FROM prj WHERE prj.isactive=".ISACTIVE_ACTIVE." and prj.prj_status_id=".PRJ_STATUS_ONGOING);
		while($row = $res->fetch()){
			$projects[$row['id']] = $row;
		}
		$view_params['projects'] = $projects;
		return $view_params;
	}
	
	/*
	1. 根据testcase_id和prj_id，确定每个Case的Ver_id
	2. 对于每个Ver_id，
		1）查看目前关联的prj：Current_prj
		2）比较current_prj和要关联的prj，得到new_prj和total_prj，其中new_prj是新增的，total_prj是总的
	3. 删除这个Case和新增prj的连接，因为可能已经挂接在其他Version上
	4. 如果是Link，则增加Ver_id 和prj的连接
	5. 添加History记录
	*/
	protected function handlePost(){
		$params = $this->params;
		$table_id = $this->get('table').'_id';
		$ver_id = $this->ver_table.'_id';
		$t_ids = implode(',', $params['id']);
		$edit_statuses = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
// print_r($this->params);
		if(!empty($params['from']) && $params['from'] == $this->ver_table){
			$sql = "SELECT link.* FROM {$this->link_table} link left join {$this->ver_table} ver on link.$ver_id=ver.id".
				" WHERE prj_id={$params['prj_id']} AND $ver_id IN ($t_ids) AND ver.edit_status_id IN ($edit_statuses)";
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
				$tmp = $this->tool->query("SELECT group_concat(distinct {$this->link_field}) as prj_ids from {$this->link_table} where $ver_id={$row[$ver_id]} AND edit_status_id IN ($edit_statuses)");
				$tmpRow = $tmp->fetch();
				$current_prjs = explode(',', $tmpRow['prj_ids']);
				if ($params['link'] == 'link'){
					$new_prj = array_diff($params['projects'], $current_prjs); //这是要添加的记录。添加前要先删除原有记录
					$last_prj = array_merge($current_prjs, $new_prj); //最终应挂接的projects
				}
				else{ // drop
					$new_prj = array_intersect($params['projects'], $current_prjs); // 这是要删除的记录
					$last_prj = array_diff($current_prjs, $params['projects']); //最终应挂接的projects
				}
	// print_r($new_prj);
	// print_r($last_prj);
				if (!empty($new_prj)){
					$strNewPrj = implode(',', $new_prj);
					$where = "{$this->link_field} IN ($strNewPrj) AND $table_id={$row[$table_id]} AND edit_status_id IN ($edit_statuses)";
					// 同一个Case的连接要转移到指定的Version，所以要先删除原有的连接记录
					// 在prj_testcase_ver_history里插入相应记录
					$sql = "INSERT INTO {$this->history_table} ({$this->link_field}, $table_id, $ver_id, act) ".
						" SELECT {$this->link_field}, $table_id, $ver_id, 'remove'".
						" FROM {$this->link_table}".
						" WHERE $where";
	// print_r($sql);			
					$this->db->query($sql);
					//在link表里删除new_prj + version
					$this->tool->delete($this->link_table, $where, $this->get('db'));
				}
				if($params['link'] == 'link'){
					// 在prj_testcase_ver_history里插入相应记录
					foreach($new_prj as $e){
						$this->tool->insert($this->history_table, array($this->link_field=>$e, $table_id=>$row[$table_id], $ver_id=>$row[$ver_id], 'act'=>'link'), $this->get('db'));
						$row[$this->link_field] = $e;
						unset($row['created']);
						unset($row['id']);
	// print_r($row);					
						$this->tool->insert($this->link_table, $row, $this->get('db'));
					}
				}
			}
		}
		else{
			$sql = "SELECT * FROM {$this->link_table} ".
				" WHERE prj_id={$params['prj_id']} AND $table_id IN ($t_ids) AND edit_status_id IN ($edit_statuses)";
			$res = $this->tool->query($sql);
			while($row = $res->fetch()){
				$tmp = $this->tool->query("SELECT group_concat(distinct {$this->link_field}) as prj_ids from {$this->link_table} where $ver_id={$row[$ver_id]} AND edit_status_id IN ($edit_statuses)");
				$tmpRow = $tmp->fetch();
				$current_prjs = explode(',', $tmpRow['prj_ids']);
				if ($params['link'] == 'link'){
					$new_prj = array_diff($params['projects'], $current_prjs); //这是要添加的记录。添加前要先删除原有记录
					$last_prj = array_merge($current_prjs, $new_prj); //最终应挂接的projects
				}
				else{ // drop
					$new_prj = array_intersect($params['projects'], $current_prjs); // 这是要删除的记录
					$last_prj = array_diff($current_prjs, $params['projects']); //最终应挂接的projects
				}
	// print_r($new_prj);
	// print_r($last_prj);
				if (!empty($new_prj)){
					$strNewPrj = implode(',', $new_prj);
					$where = "{$this->link_field} IN ($strNewPrj) AND $table_id={$row[$table_id]} AND edit_status_id IN ($edit_statuses)";
					// 同一个Case的连接要转移到指定的Version，所以要先删除原有的连接记录
					// 在prj_testcase_ver_history里插入相应记录
					$sql = "INSERT INTO {$this->history_table} ({$this->link_field}, $table_id, $ver_id, act) ".
						" SELECT {$this->link_field}, $table_id, $ver_id, 'remove'".
						" FROM {$this->link_table}".
						" WHERE $where";
	// print_r($sql);			
					$this->db->query($sql);
					//在link表里删除new_prj + version
					$this->tool->delete($this->link_table, $where, $this->get('db'));
				}
				if($params['link'] == 'link'){
					// 在prj_testcase_ver_history里插入相应记录
					foreach($new_prj as $e){
						$this->tool->insert($this->history_table, array($this->link_field=>$e, $table_id=>$row[$table_id], $ver_id=>$row[$ver_id], 'act'=>'link'), $this->get('db'));
						$row[$this->link_field] = $e;
						unset($row['created']);
						unset($row['id']);
	// print_r($row);					
						$this->tool->insert($this->link_table, $row, $this->get('db'));
					}
				}
			}
		}
		return;
	}
}

?>