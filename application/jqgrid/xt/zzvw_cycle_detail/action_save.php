<?php

require_once(APPLICATION_PATH.'/jqgrid/action_save.php');

class xt_zzvw_cycle_detail_action_save extends action_save{

    protected function _saveOne($db, $table, $pair){
		$table = 'cycle_detail';
		$real_table = $this->get('real_table');
		if ($this->get('table') == $table && $real_table != $table){
			$table = $this->get('real_table');
		}
		$pair = $this->tool->extractData($pair, $table, $db);
    	if (empty($pair['id'])){
			return $this->newRecord($db, $table, $pair);
    	}
		return $this->updateRecord($db, $table, $pair);
    }
	
	protected function updateRecord($db, $table, $pair){		
		$data = array();
		if(!empty($pair['test_env_id']))
			$data['test_env_id'] = $pair['test_env_id'];
		if(!empty($pair['result_type_id'])){
			if('-1' == $pair['result_type_id'])
				$pair['result_type_id'] = 0;
			$data['result_type_id'] = $pair['result_type_id'];
		}
		if(!empty($pair['defect_ids']))
			$data['defect_ids'] = $pair['defect_ids'];
		else
			$data['defect_ids'] = '';
		if(!empty($pair['comment'])){
			$author = $this->userInfo->nickname;
			$data['comment'] = $author.":".mysql_real_escape_string($pair['comment']);
		}
		else
			$data['comment'] = '';
		$data['finish_time'] = date("Y-m-d H:i:s");
		$this->tool->update('cycle_detail', $data, "id=".$pair['id']);	
// print_r($pair);
		if(!empty($pair['result_type_id']))
			$this->tool->update('testcase_last_result', array('result_type_id'=>$pair['result_type_id'], 
				'cycle_detail_id'=>$pair['id'], 'tested'=>$data['finish_time']), "id=".$pair['id']);
		if(!empty($pair['new_issue_comment'])){
			$this->addfeildnew($pair['id'], $pair['new_issue_comment'], 'issue_comment');
		}
		//logfile，存放在php端的logfile文件夹中
		//$filename = '';
		
		//发送至CQ的处理，waiting
		/*if(!empty($params['submit_a_cr'])){
				
			}
		}*/
		return $pair['id'];
	}
	private function addfeildnew($id, $data, $feild){
		$author = $this->userInfo->nickname;
		$res = $this->tool->query("SELECT ".$feild." FROM cycle_detail WHERE id=".$id);
		if($info = $res->fetch()){
			if(empty($info[$feild])){
				$sql = "UPDATE cycle_detail SET ".$feild."='".$author.
				":".date('Y-m-d H:i:s')."--".mysql_real_escape_string($data)."' WHERE id=".$id;
			}
			else{
				$sql = "UPDATE cycle_detail SET ".$feild."=concat('".$author.
				":".date('Y-m-d H:i:s')."--','".mysql_real_escape_string($data)."', ".$feild.", '"."\r\n"."') WHERE id=".$id;
			}
			$res = $this->tool->query($sql);
		}
	}
}