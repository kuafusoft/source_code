<?php
require_once('action_jqgrid.php');

class xt_testcase_ver_action_link2prj extends action_jqgrid{
	protected function handlePost(){
print_r($this->params);
		// $this->_link2prj($this->params);
	}
	
	protected function getViewParams($params){
print_r($params);
		$view_params = $params;
		$view_params['view_file'] = "testcase_ver_link2prj.phtml";
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase_ver';

		$projects = array();
		$res = $this->tool->query("SELECT prj.id, prj.name FROM prj WHERE prj.isactive=".ISACTIVE_ACTIVE." and prj.prj_status_id=".PRJ_STATUS_ONGOING);
		while($row = $res->fetch()){
			$projects[$row['id']] = $row;
		}
		$view_params['projects'] = $projects;
		return $view_params;
	}
	
	protected function _link2prj($params){//$vers, $prj_ids, $link = 'link', $note = ''){
		$strTestcase_ids = implode(',', json_decode($params['id']));
		if (!isset($params['note'])) $params['note'] = '';
		$edit_statuses = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
//print_r($params);
		if ($params['from'] == 'testcase'){
			$sql = "SELECT *, group_concat(prj_id) as prj_ids FROM prj_testcase_ver WHERE testcase_id IN ($strTestcase_ids) AND prj_id={$params['prj_id']} group by testcase_ver_id";
		}
		else
			$sql = "SELECT *, group_concat(prj_id) as prj_ids FROM prj_testcase_ver WHERE testcase_ver_id in ($strTestcase_ids) group by testcase_ver_id";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$tmp = $this->tool->query("SELECT group_concat(distinct prj_id) as prj_ids from prj_testcase_ver where testcase_id={$row['testcase_ver_id']}");
			$tmpRow = $tmp->fetch();
			$row['prj_ids'] = $tmpRow['prj_ids'];
//print_r($row);		
			$current_prjs = explode(',', $row['prj_ids']);
//print_r($current_prjs);		
//print_r($params['projects']);
			if ($params['link'] == 'link'){
				$new_prj = array_diff($params['projects'], $current_prjs); //这是要添加的记录。添加前要先删除原有记录
				$last_prj = array_merge($current_prjs, $new_prj); //最终应挂接的projects
			}
			else{
				$new_prj = array_intersect($params['projects'], $current_prjs); // 这是要删除的记录
				$last_prj = array_diff($current_prjs, $params['projects']); //最终应挂接的projects
			}
//print_r($new_prj);
//print_r($last_prj);
			if (!empty($new_prj)){
				// 删除记录
				// 在prj_testcase_ver_history里插入相应记录
				$sql = "INSERT INTO prj_testcase_ver_history (prj_id, testcase_id, testcase_ver_id, act, note) ".
					" SELECT prj_id, testcase_id, testcase_ver_id, 'remove', ".$this->db->quote($params['note']).
					" FROM prj_testcase_ver".
					" WHERE testcase_id={$row['testcase_id']} AND prj_id in (".implode(',', $new_prj).")";
//	print_r($sql);			
				$this->tool->query($sql);
				//在prj_testcase_ver里删除new_prj + version
				$this->tool->delete('prj_testcase_ver', 'prj_id in ('.implode(',', $new_prj).') AND testcase_id='.$row['testcase_id']);
			}
			if($params['link'] == 'link'){
				// 在prj_testcase_ver_history里插入相应记录
				foreach($last_prj as $e){
					$this->tool->insert('prj_testcase_ver_history', array('prj_id'=>$e, 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$row['testcase_ver_id'], 'act'=>'link', 'note'=>$params['note']));
					$this->tool->insert('prj_testcase_ver', array('prj_id'=>$e, 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$row['testcase_ver_id'], 'note'=>$params['note'], 
						'edit_status_id'=>$row['edit_status_id'], 'testcase_priority_id'=>$row['testcase_priority_id'], 'owner_id'=>$row['owner_id']));
				}
			}
		}
		return;
	}
}

?>