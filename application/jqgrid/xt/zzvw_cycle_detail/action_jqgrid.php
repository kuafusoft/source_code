<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_jqgrid extends action_jqgrid{
	protected function init(&$controller){
		parent::init($controller);
		if(in_array('admin', $this->userInfo->roles) || in_array('assistant_admin', $this->userInfo->roles))
			$this->userInfo->isAdmin = true;
	}
	protected function caclIDs($params){
		//$params['id'] = json_decode($params['id']);//检查$params是否非空
		$params['c_f'] = json_decode($params['c_f']);
		if(is_array($params['c_f']))
			$params['c_f'] = array_unique($params['c_f']);
		if(count($params['c_f']) == 1){
			if($params['c_f'][0] == '1'){
				foreach($params['id'] as $k=>$v){
					if(!empty($v)){
							//是虚行，找到codec_stream_id， 找到对应id
						$res = $this->tool->query("SELECT cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v);
						if($info = $res->fetch()){
							if(!empty($info['codec_stream_id'])){
								$sql = "SELECT id FROM ".$this->get('table')." WHERE cycle_id=".$info['cycle_id'].
									" AND codec_stream_id=".$info['codec_stream_id'].
									" AND test_env_id=".$info['test_env_id'];
								// foreach($params as $key=>$val){
									// if($key != "id" && $key != "element" && $key != "c_f" && $key != "flag" && $key != "codec_stream_name" && $key != "cycle_id" && $key != "replaced" && $key != "logfile_upload" && $key != "new_comment" && $key != "purpose" && $key != "cellName" && $key != "code" && $key != "new_issue_comment"){
										// if(!empty($val)){
											// $str = " AND ".$key."=".$val;
											// $sql .= $str;
										// }
									// }
								// }
								$detail_res = $this->tool->query($sql);
								while($detail = $detail_res->fetch())
									$elements[] = $detail['id'];
							}
						}
					}
				}
			}
			else
				$elements = $params['id'];
		}
		else
			$elements = 'error';
		
		return $elements;
	}
	
	protected function set_feild($data, $fvals, $cycle_id, $feild){
		if(!empty($cycle_id)){//可以去掉
			if($feild == 'test_env_id')
				$cond = 'codec_stream_id';
			else if($feild == 'codec_stream_id'){
				$cond = 'test_env_id';
			}
			$condition = " testcase_id={$data['testcase_id']} AND testcase_ver_id={$data['testcase_ver_id']} AND prj_id={$data['prj_id']}".
				 " AND compiler_id={$data['compiler_id']} AND build_target_id={$data['build_target_id']}";
			if($data[$cond])
				$condition .= " AND ".$cond."={$data[$cond]}";
			$condition .= " AND cycle_id={$cycle_id}";
			$d_res = $this->tool->query("SELECT ".$feild." FROM cycle_detail WHERE".$condition);
			while($row = $d_res->fetch()){
				foreach($fvals as $k=>$val){
					if($val == $row[$feild])
						unset($fvals[$k]);
				}
			}
			$d_res = $this->tool->query("SELECT id, ".$feild." FROM cycle_detail WHERE".$condition);
			//查询此条件下该记录的test_env_id是否为空，如果为空，update
			while($row = $d_res->fetch()){
				if(empty($row[$feild])){
					foreach($fvals as $k=>$val){
						if(!empty($val)){
							$this->tool->update('cycle_detail', array($feild=>$val), "id=".$row['id']);
							unset($fvals[$k]);
						}
					}
				}
			}
			if(!empty($fvals)){
				foreach($fvals as $val){
					//insert剩下env的记录
					if($val != $data[$feild]){
						$data = array('cycle_id'=>$cycle_id, 'testcase_id'=>$data['testcase_id'], 'prj_id'=>$data['prj_id'], 'compiler_id'=>$data['compiler_id'],
							'build_target_id'=>$data['build_target_id'], 'testcase_ver_id'=>$data['testcase_ver_id'], 'result_type_id'=>0, $cond=>$data[$cond], $feild=>$val, 'finish_time'=>0);
						$this->tool->insert('cycle_detail', $data);
					}
				}
			}
		}
	}
	
	protected function updatelastresult($id, $result){
		$res = $this->tool->query("SELECT detail.id as id, detail.testcase_id as testcase_id, detail.codec_stream_id as codec_stream_id, detail.prj_id as prj_id, cycle.rel_id as rel_id".
			" FROM cycle_detail detail LEFT join cycle on cycle_id=cycle.id WHERE detail.id=".$id);			
		while($row = $res->fetch()){
			$tcres = $this->tool->query("SELECT id FROM testcase_last_result WHERE testcase_id=".$row['testcase_id']." AND prj_id=".$row['prj_id']." AND rel_id=".$row['rel_id']." AND codec_stream_id=".$row['codec_stream_id']);
			if($data = $tcres->fetch())
				$this->tool->update('testcase_last_result', array('result_type_id'=>$result, 'cycle_detail_id'=>$row['id'], 'codec_stream_id'=>$row['codec_stream_id'], 'tested'=>date("Y-m-d H:i:s")), "id=".$data['id']);
			else
				$this->tool->insert('testcase_last_result', array('testcase_id'=>$row['testcase_id'], 'cycle_detail_id'=>$row['id'], 'result_type_id'=>$result, 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'codec_stream_id'=>$row['codec_stream_id'], 'tested'=>date("Y-m-d H:i:s")));
			$this->tool->update('testcase', array('last_run'=>date("Y-m-d H:i:s")), "id=".$row['testcase_id']);	
		}
	}
	
}

?>