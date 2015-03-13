<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_link2prj.php');

class xt_testcase_ver_action_link2prj extends action_ver_link2prj{

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
		// print_r($this->params);
		$params = $this->params;
		$res = $this->tool->query("SELECT * FROM testcase_ver where id={$params['id']}");
		$ver = $res->fetch();
		$ver_published = in_array($ver['edit_status_id'], array(EDIT_STATUS_PUBLISHED, EDIT_STATUS_GOLDEN));
		
		$prj_ids = implode(',', $params['projects']);
		$edit_statuses = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
		$sql = "SELECT group_concat(link.prj_id) as prj_ids FROM prj_testcase_ver link".
			" WHERE link.testcase_id={$ver['testcase_id']}";
		$res = $this->tool->query($sql);
		$row = $res->fetch();
		$current_prjs = $row['prj_ids'];
		$new_prjs = $params['projects'];
		if(!empty($current_prjs)){
			$current_prjs = explode(',', $current_prjs);
			$new_prjs = array_diff($params['projects'], $current_prjs);
		}
		if(!empty($new_prjs)){
			foreach($new_prjs as $prj_id){
				$this->tool->insert("prj_testcase_ver_history", array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'testcase_ver_id'=>$ver['id'], 'act'=>'add', 'note'=>''));
				$this->tool->insert("prj_testcase_ver", array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'testcase_ver_id'=>$ver['id']));
			}
		}
		
		$sql = "SELECT link.id, link.prj_id, link.testcase_id, link.testcase_ver_id, ver.edit_status_id ".
			" FROM prj_testcase_ver link left join testcase_ver ver on link.testcase_ver_id=ver.id".
			" WHERE link.testcase_id={$ver['testcase_id']} and link.testcase_ver_id!={$params['id']} and prj_id in ($prj_ids)";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			//如果ver['edit_status_id']是published，则需要考虑删除连接的问题
			if($ver_published && in_array($row['edit_status_id'], array(EDIT_STATUS_PUBLISHED, EDIT_STATUS_GOLDEN))){ //如果也是published，则需要断开连接
				$this->tool->insert("prj_testcase_ver_history", array('prj_id'=>$row['prj_id'], 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$row['testcase_ver_id'], 'act'=>'remove', 'note'=>''));
				$this->tool->delete('prj_testcase_ver', "id={$row['id']}");
			}
			$this->tool->insert("prj_testcase_ver_history", array('prj_id'=>$row['prj_id'], 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$ver['id'], 'act'=>'add', 'note'=>''));
			$this->tool->insert('prj_testcase_ver', array('prj_id'=>$row['prj_id'], 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$ver['id']));
		}
		return;
	}
};


?>