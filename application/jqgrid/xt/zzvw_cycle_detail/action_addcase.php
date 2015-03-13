<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_addcase extends action_jqgrid{

   public function handlePost(){
		//只添加case，env和codec在case 添加成功之后在detail中添加
		//$params = $this->parseParams();
		$params = $this->params;
		//$params['id'] = json_decode($params['id']);
		$real_table = $this->get('real_table');
		$sql = "SELECT prj_ids, compiler_ids, build_target_ids, test_env_id, testcase_type_ids FROM cycle WHERE id=".$params['parent'];
		$res = $this->tool->query($sql);
		if($info = $res->fetch()){
			$cycle = $info;
		}
		else
			return "error";
		if(stripos($cycle['prj_ids'], ",") !== false)
			$cycle['prj_ids'] = explode(",", $cycle['prj_ids']);
		else
			$cycle['prj_ids'] = array($cycle['prj_ids']);
		if(stripos($cycle['testcase_type_ids'], ",") !== false)
			$cycle['testcase_type_ids'] = explode(",", $cycle['testcase_type_ids']);
		else
			$cycle['testcase_type_ids'] = array($cycle['testcase_type_ids']);
		if(stripos($cycle['compiler_ids'], ",") !== false)
			$cycle['compiler_ids'] = explode(",", $cycle['compiler_ids']);
		else
			$cycle['compiler_ids'] = array($cycle['compiler_ids']);
		if(stripos($cycle['build_target_ids'], ",") !== false)
			$cycle['build_target_ids'] = explode(",", $cycle['build_target_ids']);
		else
			$cycle['build_target_ids'] = array($cycle['build_target_ids']);
			
		$sql = "SELECT prj_testcase_ver.prj_id, prj_testcase_ver.testcase_ver_id, prj_testcase_ver.testcase_id FROM prj_testcase_ver 
			left join testcase_ver on testcase_ver.id = prj_testcase_ver.testcase_ver_id
			left join testcase on testcase.id = prj_testcase_ver.testcase_id
			where prj_testcase_ver.prj_id in (".implode(",", $cycle['prj_ids']).
			") and prj_testcase_ver.testcase_id in (".implode(",", $params['id']).")".
			" and testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")".
			" and testcase.isactive = 1";	
		$res = $this->tool->query($sql);
// print_r($params['id']);
		while($row = $res->fetch()){
// print_r($row);
			// $isexist = false;
			//默认case页只有一条case，不计env，因为可以在add env里面添加
			foreach($cycle['compiler_ids'] as $compiler_id){
				if(empty($compiler_id))continue;
				foreach($cycle['build_target_ids'] as $build_target_id){
					if(empty($build_target_id))continue;
					$detail_sql = "SELECT * FROM cycle_detail WHERE cycle_id = {$params['parent']}".
						" AND testcase_id = {$row['testcase_id']} AND prj_id = {$row['prj_id']}".
						" AND compiler_id = {$compiler_id} AND build_target_id = {$build_target_id}";//compiler + test_env + build_target
					$detail_res = $this->tool->query($detail_sql);
					if($detail_row = $detail_res->fetch()){
						// $isexist = true;
						//ver不等时，update
						if(empty($detail_row['codec_stream_id'])){
							$datas = array();
							if ($detail_row['testcase_ver_id'] != $row['testcase_ver_id'])
								$datas['testcase_ver_id'] = $row['testcase_ver_id'];
							//如果result_type_id不为0时，如果replaced，则置0，
							if ($detail_row['result_type_id'] != 0){
								if ($params['replaced']){//replace所有case的result_type_id为0
									$datas['result_type_id'] = 0;
									$datas['finish_time'] = 0;
								}
							}
		// print_r($datas);
		// print_r($detail_row);
							if(!empty($datas))
								$this->tool->update($real_table, $datas, "id=".$detail_row['id']);
						}
					}
					else{
						$data = array('cycle_id'=>$params['parent'], 'testcase_ver_id'=>$row['testcase_ver_id'], 'testcase_id'=>$row['testcase_id'], 
							'result_type_id'=>0, 'codec_stream_id'=>0, 'test_env_id'=>$cycle['test_env_id'], 'compiler_id'=>$compiler_id, 
							'build_target_id'=>$build_target_id, 'prj_id'=>$row['prj_id'], 'finish_time'=>0);
						$this->tool->insert($real_table, $data);
					}
				}
			}
		}
		// $this->processCycle($params['parent']);
	}
	private function processCycle($cycle_id){
		$res = $this->tool->query("select group_concat(distinct detail.prj_id) as prj_ids, group_concat(distinct detail.compiler_id) as compiler_ids, 
			group_concat(distinct detail.build_target_id) build_target_ids, group_concat(distinct tc.testcase_type_id) as testcase_type_ids
			from cycle_detail detail left join testcase tc on tc.id = detail.testcase_id where detail.cycle_id = $cycle_id");
		if($row = $res->fetch()){
			$this->tool->update("cycle", array('prj_ids'=>$row['prj_ids'], 'compiler_ids'=>$row['compiler_ids'], 'build_target_ids'=>$row['build_target_ids'],
				'testcase_type_ids'=>$row['testcase_type_ids']), 'id='.$cycle_id
			);
		}
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Add Type';
		$view_params['view_file'] = 'add_case_type.phtml';
		$view_params['view_file_dir'] = '/jqgrid/xt/' + $this->get('table');
		$view_params['blank'] = 'false';
		return $view_params;
	}
}

?>