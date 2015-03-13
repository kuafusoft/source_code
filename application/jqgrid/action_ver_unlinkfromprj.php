<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_action.php');

class action_ver_unlinkfromprj extends action_ver_action{
	protected function getViewParams($params){
		if(is_array($params['id']))
			$params['id'] = implode(',', $params['id']);
		$view_params = $params;
		$view_params['view_file'] = "unlinkfromprj.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

// print_r($params);
		$projects = array();
		$sql = "SELECT prj.id, prj.name ".
			" FROM prj left join prj_testcase_ver on prj.id=prj_testcase_ver.prj_id".
			" WHERE prj.isactive=".ISACTIVE_ACTIVE." and prj.prj_status_id=".PRJ_STATUS_ONGOING.
			" AND prj_testcase_ver.testcase_id IN ({$params['id']})".
			" ORDER BY name ASC";
// print_r($sql);
		$res = $this->tool->query($sql);
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
		$table_id = $this->get('table').'_id';
		$ver_id = $this->ver_table.'_id';
		$params = $this->params;
// print_r($params);
		$strPrj = implode(',', $params['projects']);
		$t_ids = implode(',', $params['id']);
		$edit_statuses = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;

		$sql = "INSERT INTO {$this->history_table} ({$this->link_field}, $table_id, $ver_id, act) ".
			" SELECT {$this->link_field}, $table_id, $ver_id, 'remove'".
			" FROM {$this->link_table}".
			" WHERE prj_id IN ($strPrj) AND $table_id IN ($t_ids) AND edit_status_id IN ($edit_statuses)";
// print_r($sql)			;
		$this->db->query($sql);
		$sql = "DELETE FROM {$this->link_table}".
			" WHERE prj_id IN ($strPrj) AND $table_id IN ($t_ids) AND edit_status_id IN ($edit_statuses)";
// print_r($sql)			;
		$this->tool->query($sql);
		return;
	}
}

?>