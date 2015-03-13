<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_add_del_trickmode extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		$params = $this->parseParams();
		$cycle = '';
		$params['id'] = json_decode($params['id']);//检查$params是否非空
		$params['actions'] = json_decode($params['actions']);
		$actions = implode(",", $params['actions']);
		$params['c_f'] = json_decode($params['c_f']);
		$stream = array();
		$id = array();
		$res = $this->db->query("SELECT test_env_id, prj_id FROM cycle WHERE id=".$params['cycle_id']);
		$cycle = $res->fetch();
		foreach($params['id'] as $k=>$v){
			if(!empty($v)){
				if($params['c_f'][$k]==1){
					//是虚行，找到codec_stream_id， 找到对应id
					$res = $this->db->query("SELECT codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v);
					$info = $res->fetch();
					if($params['isDel'] == 1){
						$sql = "SELECT id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
							" AND codec_stream_id=".$info['codec_stream_id'];
						if(!empty($info['test_env_id']))
							$sql .= " AND test_env_id=".$info['test_env_id'];
						$sql .= " AND testcase_id in ($actions)";	
						$res = $this->db->query($sql);
						while($data = $res->fetch()){
							$id[] = $data['id'];
						}	
					}
					else if($params['isDel'] == 0){
						foreach($params['actions'] as $case){
							$res = $this->db->query("SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id=".$case." AND prj_id=".$cycle['prj_id']." AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")");
							$ver = $res->fetch();
							$d_sql = "SELECT id, testcase_ver_id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
								" AND testcase_id=".$case.
								" AND codec_stream_id=".$info['codec_stream_id'].
								" AND test_env_id=".$info['test_env_id'];
							$res = $this->db->query($d_sql);
							if($d_info = $res->fetch()){//查看是否已有记录,如果有,更新到最新的ver
								if($d_info['testcase_ver_id'] == $ver['testcase_ver_id'])
									continue;
								$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id']);
								$this->db->update('cycle_detail', $data, 'id='.$d_info['id']);
							}
							else{
								$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id'], 'testcase_id'=>$case,
									'result_type_id'=>0, 'test_env_id'=>$info['test_env_id'], 'codec_stream_id'=>$info['codec_stream_id'], 'finish_time'=>0);
								$this->db->insert('cycle_detail', $data);
							}

						}
					}
				}
			}
		}
		if($params['isDel'] == 1){
			if(!empty($id)){
				$id = implode(",", $id);
				$this->db->delete('cycle_detail_step', "cycle_detail_id in (".$id.")");
				$this->db->delete('cycle_detail', "id in (".$id.") AND cycle_id = {$params['cycle_id']}");
			}
			print_r('success');
		}
	}
	
}

?>