<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail_stream/action_jqgrid.php');

class xt_zzvw_cycle_detail_stream_action_addCaseForCodec extends xt_zzvw_cycle_detail_stream_action_jqgrid{

	public function handlePost(){
		$params = $this->parseParams();
		//$params['id'] = json_decode($params['id']);
		$params['testcase_id'] = json_decode($params['testcase_id']);
//print_r($params['testcase_id']);
		$res = $this->tool->query("SELECT prj_id, testcase_type_id, test_env_id FROM cycle WHERE id=".$params['parent']);
		$cycle = $res->fetch();
// print_r(count($params['id']));		
		foreach($params['id'] as $streamid){
			//static $caseinfo = array();
			$trickmodes = $actions = array();
			$s_sql = "select stream.testcase_ids as trickmode_ids, type.testcase_ids as testcase_ids".
				" from codec_stream stream left join codec_stream_type type on type.id = stream.codec_stream_type_id".
				" where stream.id=".$streamid;
			$s_res = $this->tool->query($s_sql);
			if($info = $s_res->fetch()){
				if(!empty($info['trickmode_ids'])){
// print_r($info['trickmode_ids']);
					$actions = explode(',', $info['trickmode_ids']);
					foreach($actions as $tm){
						if(in_array($tm, $params['testcase_id']))
							$trickmodes[] = $tm;
					}
				}
				if(empty($trickmodes)){
					if(!empty($info['testcase_ids'])){
// print_r($info['testcase_ids']);
							$cases = explode(',', $info['testcase_ids']);
							foreach($cases as $tc){
								if(in_array($tc, $params['testcase_id']))
									$trickmodes[] = $tc;
							}
						}
					}
				}
			}
			if(!empty($trickmodes)){
// print_r($trickmodes);
				$case_sql = "select ptv.testcase_id, ptv.testcase_ver_id from prj_testcase_ver ptv".
					" left join testcase on testcase.id = ptv.testcase_id".
					" where ptv.testcase_id in (".implode(",", $trickmodes).") and ptv.prj_id = ".$cycle['prj_id'].
					" and testcase.testcase_type_id = ".$cycle['testcase_type_id'];
				$case_res = $this->tool->query($case_sql);
				$datas = array();
				while($case = $case_res->fetch()){
					if($case['testcase_ver_id']){
						$sql = "SELECT id, testcase_ver_id, result_type_id FROM cycle_detail WHERE cycle_id=".$params['parent'].
							" AND testcase_id=".$case['testcase_id'].
							" AND test_env_id=".$params['test_env_id'].
							" AND codec_stream_id=".$streamid;
						$res = $this->tool->query($sql);
						if($info = $res->fetch()){
							//其实不需要查testcase_ver_id,因为case是不可用的，就只是作为add stream用的
							if ($info['testcase_ver_id'] != $case['testcase_ver_id'])
								$datas['testcase_ver_id'] = $case['testcase_ver_id'];
							//如果result_type_id不为0时，如果replaced，则置0，
							if ($info['result_type_id'] != 0){
								if ($params['replaced']){//replace所有case的result_type_id为0
									$datas['result_type_id'] = 0;
									$datas['finish_time'] = 0;
								}
							}
							if(!empty($datas))
								$this->tool->update('cycle_detail', $datas, "id=".$info['id']);
						}
						else{
							$data = array('cycle_id'=>$params['parent'], 'testcase_ver_id'=>$case['testcase_ver_id'], 'testcase_id'=>$case['testcase_id'], 
								'result_type_id'=>0, 'test_env_id'=>$params['test_env_id'], 'codec_stream_id'=>$streamid, 'finish_time'=>0);
							$this->tool->insert('cycle_detail', $data);
						}
					}
				}
			}
		}
print_r("done");
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Codec';
		$view_params['view_file'] = 'newElement.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		
		$cols = array();
		$sql = "select prj_id, testcase_type_id, test_env_id from cycle where id = ".$params['parent'];
		$res = $this->tool->query($sql);
		$info = $res->fetch();
// print_r($info);
		if($info['testcase_type_id']== 2)//codec
			$testcase_module_id = 507;//$testcase_module_id = 507;
		else if($info['testcase_type_id'] == 14)//fas
			$testcase_module_id = 467;//$testcase_module_id = 467;
		$cart_data = new stdClass;
		$cart_data->filters = '{"groupOp":"AND","rules":[{"field":"testcase_type_id","op":"eq","data":'.$info['testcase_type_id'].'}, 
			{"field":"testcase_module_id","op":"eq","data":'.$testcase_module_id.'}, {"field":"prj_id","op":"eq","data":'.$info['prj_id'].'}]}';
		$cols[] = array('id'=>'testcase_id', 'name'=>'testcase_id', 'label'=>'Actions', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>array()), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'testcase', 'cart_data'=>json_encode($cart_data), 'editrules'=>array('required'=>true));
		$res = $this->tool->query("SELECT id, name FROM test_env");
		$env = array();
		$env[0] = '';
		while($row = $res->fetch()){
			$env[$row['id']] = $row['name'];
		}
		$cols[] = array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>$env), 'type'=>'select', 'defval'=>$info['test_env_id'], 'editrules'=>array('required'=>true));
		$view_params['cols'] = $cols;
		return $view_params;
	}
	
}

?>