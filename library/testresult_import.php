<?php
	require_once('xt_common.php');
	
	class testresult_import extends xt_common{
		public function __construct($dsn){
			parent::__construct($dsn);
		}
		
		public function import_testresult($cycleInfo, $importData){
			$cycleName = '';
			$cycle_id = $this->createCycle($cycleInfo, $cycleName);
			foreach($importData as $sheet_title=>$data){
				if (empty($data['format']))
					$data['format'] = 'normal';
				$method = 'import_'.$data['format'].'Result';
				if (method_exists($this, $method))
					$this->{$method}($cycle_id, $data);
				else
					$this->import_normalResult($cycle_id, $data);
			}
		}

		public function import_testcase($importData, $prj_ids){
			foreach($importData as $sheet_title=>$data){
				if (empty($data['format']))
					$data['format'] = 'normal';
				$method = 'import_'.$data['format'].'Testcase';
				if (method_exists($this, $method))
					$this->{$method}($data, $prj_ids);
				else
					$this->import_normalTestcase($data, $prj_ids);
			}
		}
		
		// result = array('result'=>'', 'comment'=>'', 'env'=>array(), 'finish_time', 'duration_minutes');
		protected function getCycleDetailInfo($cycle_id, $testcaseInfo, $result, $stream_id = 0){
			// prj_testcase_ver and prj_testcase_ver_history
			$res = $this->db->query("SELECT * FROM testcase_ver where id=".$testcaseInfo['testcase_ver_id']);
			$case_ver = $res->fetch();
			
			$res = $this->db->query("SELECT * FROM cycle where id=$cycle_id");
			$cycle = $res->fetch();
			
			$this->prj_testcase_ver($cycle['prj_id'], $case_ver); 

			$ret = array();
			if (empty($result['env']))
				$result['env'] = array("default env"=>"default env");
				
			$result_type_id = $this->getResultId($result['result']);
				
			$detail = array('cycle_id'=>$cycle_id, 
				'testcase_id'=>$testcaseInfo['testcase_id'], 
				'testcase_ver_id'=>$testcaseInfo['testcase_ver_id'],
				'codec_stream_id'=>$stream_id,
				'result_type_id'=>$result_type_id,
				'comment'=>isset($result['comment']) ? $result['comment'] : '',
				'finish_time'=>isset($result['finish_time']) ? $result['finish_time'] : date('Y-m-d H:i:s'),
				'duration_minutes'=>isset($result['duration_minutes']) ? $result['duration_minutes'] : 0,
			);
			foreach($result['env'] as $env){
				$env_id = $this->getElementId('test_env', array('name'=>$env));
				$detail['test_env_id'] = $env_id;
				$ret[] = $cycle_detail_id = $this->getElementId('cycle_detail', $detail, array('cycle_id', 'testcase_id', 'codec_stream_id', 'test_env_id'));
				$last_result = array('testcase_id'=>$detail['testcase_id'], 'prj_id'=>$cycle['prj_id'], 'rel_id'=>$cycle['rel_id'], 'codec_stream_id'=>$stream_id,
					'result_type_id'=>$result_type_id, 'cycle_detail_id'=>$cycle_detail_id);
				$this->getElementId('testcase_last_result', $last_result);
			}
			return $ret;
		}
		
		protected function import_normalResult($cycle_id, $data){
			if (!empty($data['test_result'])){
				foreach($data['test_result'] as $row=>$v){
					foreach($v as $col=>$result){
						$testcaseInfo = $this->getCaseInfo($data['testcase'][$row]);
						// cycle_detail
						$cycle_detail = $this->getCycleDetailInfo($cycle_id, $testcaseInfo, $result);
					}
				}
			}
		}
		
		protected function import_normalTestcase($data, $prj_ids){
print_r($data);		
			if (!empty($data['testcase'])){
				foreach($data['testcase'] as $row=>$v){
					$this->getTestcaseInfo($v, $prj_ids);
					//$testcaseInfo = $this->getCaseInfo($data['testcase'][$row]);
				}
			}
		}

	}

?>