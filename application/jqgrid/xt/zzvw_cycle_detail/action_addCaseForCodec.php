<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_addCaseForCodec extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		$params = $this->parseParams();
		$params['id'] = json_decode($params['id']);
		$params['testcase_id'] = json_decode($params['testcase_id']);
		//$params['id'] = implode(",", $params['id']);
		$res = $this->db->query("SELECT prj_id FROM cycle WHERE id=".$params['cycle_id']);
		$cycle = $res->fetch();
		foreach($params['testcase_id'] as $testcase_id){
			$t_sql = "SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id=".$testcase_id." AND prj_id=".$cycle['prj_id'].
				" AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")";
			$t_res = $this->db->query($t_sql);
			if($ver = $t_res->fetch()){
				foreach($params['id'] as $stream){	
					$sql = "SELECT id, testcase_ver_id, result_type_id FROM cycle_detail WHERE cycle_id=".$params['cycle_id'].
						" AND testcase_id=".$testcase_id.
						" AND test_env_id=".$params['test_env_id'].
						" AND codec_stream_id=".$stream;
					$res = $this->db->query($sql);
					if($info = $res->fetch()){
						//其实不需要查testcase_ver_id,因为case是不可用的，就只是作为add stream用的
						$datas = array();
						if ($info['testcase_ver_id'] != $ver['testcase_ver_id'])
							$datas['testcase_ver_id'] = $ver['testcase_ver_id'];
						//如果result_type_id不为0时，如果replaced，则置0，
						if ($info['result_type_id'] != 0){
							if ($params['replaced']){//replace所有case的result_type_id为0
								$datas['result_type_id'] = 0;
								$datas['finish_time'] = 0;
							}
						}
						if(!empty($datas))
							$this->db->update('cycle_detail', $datas, "id=".$info['id']);
					}
					else{
						$data = array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver['testcase_ver_id'], 'testcase_id'=>$testcase_id, 
							'result_type_id'=>0, 'test_env_id'=>$params['test_env_id'], 'codec_stream_id'=>$stream, 'finish_time'=>0);
						$this->db->insert('cycle_detail', $data);
					}
				}
			}
		}
print_r("done");
	}
	
}

?>