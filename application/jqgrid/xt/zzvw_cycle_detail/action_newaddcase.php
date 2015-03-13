<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_newaddcase extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$records = $this->caclIDs($params);
		if($records == "error")
			return "error";
		
		$sql = "SELECT prj_ids FROM cycle WHERE id=".$params['parent'];
		$res = $this->tool->query($sql);
		if($info = $res->fetch())
			$cycle['prj_id'] = explode(",", $info['prj_ids']);
		else
			return "error";
		//处理
		$sql = 'SELECT * FROM cycle_detail left join cycle on cycle.id = cycle_detail.cycle_id'.
			' left join testcase_ver on testcase_ver.id=cycle_detail.testcase_ver_id'.
			' WHERE cycle_detail.id in ('.implode(',',$records).') AND cycle_detail.cycle_id ='.$params['cycle_id'];
		$res = $this->tool->query($sql);
		//判断是否有该条记录
		while($detail = $res->fetch()){			
			//判断是否有该ver
			//testcase + prj + editstatus唯一确定一个ver，要么是publish要么是golden，不会同时存在
			//具有相同testcase_id和prj_id的不用重复去ver，只要取一次就可以了
			if(!isset($vers[$detail['testcase_id']][$detail['prj_id']])){
				$vers_sql = "SELECT * FROM prj_testcase_ver left join testcase_ver on testcase_ver.id = prj_testcase_ver.testcase_ver_id".
					" LEFT JOIN testcase on testcase.id = prj_testcase_ver.testcase_id".
					" WHERE prj_testcase_ver.testcase_id=".$detail['testcase_id']." AND prj_testcase_ver.prj_id=".$detail['prj_id'].
					" AND testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")".
					" AND testcase.isactive = 1";
// print_r($vers_sql."\n");
				$vers_res = $this->tool->query($vers_sql);
				$vers[$detail['testcase_id']][$detail['prj_id']] = $vers_res->fetch();
			}
// print_r($vers[$detail['testcase_id']][$detail['prj_id']]);
			//ver去到之后，要添加限制条件，找到对应的detail，来更新或者insert
			if ($vers[$detail['testcase_id']][$detail['prj_id']]){
				$ver = $vers[$detail['testcase_id']][$detail['prj_id']]['testcase_ver_id'];
				$sql = "SELECT * FROM cycle_detail WHERE cycle_id=".$params['parent'].
					" AND testcase_id={$detail['testcase_id']} AND test_env_id={$detail['test_env_id']}".
					" AND prj_id={$detail['prj_id']} AND compiler_id={$detail['compiler_id']}".
					" AND codec_stream_id={$detail['codec_stream_id']} AND build_target_id={$detail['build_target_id']}";
				$detail_res = $this->tool->query($sql);
// print_r($sql);
				if($detail_row = $detail_res->fetch()){//testcase + env + codec_stream 唯一确定一条result记录
// print_r('exists');
// print_r("\n");
					$datas = array();
					//ver是否是最新的
					if ($detail_row['testcase_ver_id'] != $ver)
						$datas['testcase_ver_id'] = $ver;
					//如果result_type_id不为0时，如果replaced，则置0，
					if ($detail_row['result_type_id'] != 0){
						if ($params['replaced']){//replace所有case的result_type_id为0
							$datas['result_type_id'] = 0;
							$datas['finish_time'] = 0;
						}
					}
					if(!empty($datas))
						$this->tool->update('cycle_detail', $datas, "id=".$detail_row['id']);
				}
				// else if($env){
				else {
// print_r('does not exists');
// print_r("\n");
					$data = array('cycle_id'=>$params['parent'], 'testcase_ver_id'=>$ver, 'testcase_id'=>$detail['testcase_id'], 
						'result_type_id'=>0, 'test_env_id'=>$detail['test_env_id'], 'codec_stream_id'=>$detail['codec_stream_id'], 'finish_time'=>0,
						'prj_id'=>$detail['prj_id'], 'compiler_id'=>$detail['compiler_id'], 'build_target_id'=>$detail['build_target_id']);
					$this->tool->insert('cycle_detail', $data);
				}
				unset($ver);
			}
		}	
	}
	
}

?>