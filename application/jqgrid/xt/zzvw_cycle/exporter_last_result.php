<?php
require_once('exporter_excel.php');
require_once("toolfactory.php");
defined('WITH_RESULT_STATIC_COLUMNS') || define('WITH_RESULT_STATIC_COLUMNS', 1);
defined('WITH_RESULT_DETAIL_COLUMNS') || define('WITH_RESULT_DETAIL_COLUMNS', 2);
defined('WITH_RESULT_STATIC_COLUMNS2') || define('WITH_RESULT_STATIC_COLUMNS2', 4);
defined('WITH_COVERAGE_COLUMNS') || define('WITH_COVERAGE_COLUMNS', 3);

/*
应包含一些Sheets：
1. project-compiler-module情况
2. module-project-compiler情况
3. detail：testcase-project-compile情况
4. 各个project的情况

测试结果只显示Total、Pass、Fail and Others
如果选择了多个Release，则每个Release的情况并列
*/
class xt_zzvw_cycle_exporter_last_result extends exporter_excel{
	protected $data = array();
	protected $db = null;
	protected $heads = array();
	protected $moduleSheet = 1;
	protected $cycleCount = 0;
	protected $baseData = array();
	protected $prjMerge = false;
	protected $testcase_type = array();
	protected $prj = array();
	protected $cycleInfo = array();
	protected $compiler = array();
	protected $build_target = array();
	protected $result_type = array();
	protected $test_env = array();
	protected $strIds = '';
	protected $result_header = array('total', 'pass', 'fail', 'n/a', 'n/s', 'n/t', 'ne', 'others', 'finish', 'pass_rate', 'finish_rate');
	protected $mid = array();
	protected $mid_data = array();
	protected function init($params = array()){
		parent::init($params);
// print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_cycle [export_type] => last_result [id] => array(10,9) [real_table] => cycle ) 
		$this->db = dbFactory::get($this->params['db']);
		$this->db->query("SET SESSION group_concat_max_len = 1000000");
		if(!is_array($this->params['id'])){
			$id = array();
			$id[] = $this->params['id'] ;
			$this->params['id'] = $id;
		}
		$this->strIds = implode(',', $this->params['id']);
		$this->cycleCount = count($this->params['id']);
		
		$res = $this->db->query("SELECT cycle.*, rel.name as rel FROM cycle left join rel on cycle.rel_id=rel.id WHERE cycle.id IN ({$this->strIds})");
		while($cycle = $res->fetch())
			$this->cycleInfo[$cycle['id']] = $cycle;
		
		$res = $this->db->query("SELECT distinct compiler.id, compiler.name FROM compiler");
		while($cycle = $res->fetch())
			$this->compiler[$cycle['id']] = $cycle;
			
		$res = $this->db->query("SELECT distinct build_target.id, build_target.name FROM build_target");
		while($cycle = $res->fetch())
			$this->build_target[$cycle['id']] = $cycle;
			
		$res = $this->db->query("SELECT distinct test_env.id, test_env.name FROM test_env");
		while($cycle = $res->fetch())
			$this->test_env[$cycle['id']] = $cycle;
			
		$res = $this->db->query("SELECT id, name FROM result_type");
		while($cycle = $res->fetch())
			$this->result_type[$cycle['id']] = $cycle;
		$this->result_type[0] = array('id'=>0, 'name'=>'ne');
		$this->result_type[-1] = array('id'=>-1, 'name'=>'others');
			
		// $res = $this->db->query("SELECT GROUP_CONCAT(DISTINCT testcase_type_id) as testcase_type_id FROM cycle WHERE id IN ({$this->strIds})");
		// $row = $res->fetch();
		// $this->testcase_type = explode(',', $row['testcase_type_id']);

		$res = $this->db->query("SELECT prj.id, prj.name FROM prj");
		while($row = $res->fetch()){
			$this->prj[$row['id']] = $row;
		}
		if(1){//}$this->cycleCount > 1 && count($this->prj) == 1){
			$this->prjMerge = true;
			$this->cycleInfo['merged'] = array('id'=>'merged', 'name'=>'Final Cycle', 'prj'=>$row['name'], 'rel'=>'Merged Release');
		}
		
		$this->getMidData();
		// $this->testResultData();
	}
	
	protected function getMidData(){
		$tool = toolFactory::get('kf');
		$mid1 = $this->getCodecStreamDetailMidData($this->strIds, false, true);
		$mid2 = $this->getNomalCaseDetailMidData($this->strIds, false, true);
		// $this->mid = array_merge_recursive($mid1, $mid2);
		$this->mid = $tool->array_extends($mid1, $mid2);
// print_r($mid2);
// print_r($mid1);
// print_r($this->mid);
		if($this->prjMerge){
			$mid1 = $this->getCodecStreamDetailMidData($this->strIds, true);
			$mid2 = $this->getNomalCaseDetailMidData($this->strIds, true);
			$temp = $tool->array_extends($mid1, $mid2);
			$this->mid = $tool->array_extends($this->mid, $temp);
			// $this->mid = array_merge_recursive($this->mid, $mid1, $mid2);
// print_r($mid2);
// print_r($mid1);
// print_r($this->mid);
		}
// print_r($this->mid);
		return;
	}
	
	public function setOptions($jqgrid_action){
		$this->setDefaultOptions($jqgrid_action);
		return;
		if(count($this->testcase_type) == 1){
			switch($this->testcase_type[0]){
				// case TESTCASE_TYPE_LINUX_BSP:
					// $this->setLinuxBSPOptions($jqgrid_action);
					// break;
				// case TESTCASE_TYPE_CODEC:
					// $this->setCodecOptions($jqgrid_action);
					// break;
				// case TESTCASE_TYPE_MQX:
					// $this->setMQXOptions($jqgrid_action);
					// break;
				default:
					$this->setDefaultOptions($jqgrid_action);
					break;
			
			}
		}
		else{
			$this->setDefaultOptions($jqgrid_action);
		}
	}
	
	protected function setDefaultOptions($jqgrid_action){
		$this->params['sheets'] = array();
		// $this->params['sheets'][] = $this->getCoverSheet();
		$this->params['sheets'][] = $this->getCycleSummarySheet($jqgrid_action);
		// return;
		if($this->cycleCount > 1){
			$this->params['sheets'][] = $this->getPrjCompilerModuleSheet($jqgrid_action);
			// return;
			$this->params['sheets'][] = $this->getModulePrjCompilerSheet($jqgrid_action);
		}
		// return;
		$this->params['sheets'][] = $this->getDetailSheet($jqgrid_action);
		return;
		if($this->cycleCount > 1){
			foreach($this->cycleInfo as $cycle_id=>$cycle){
				$this->params['sheets'][] = $this->getCycleSheet($jqgrid_action, $cycle_id);
			}
			if(count($this->prj) > 1){
				foreach($this->prj as $prj){
					$this->params['sheets'][] = $this->getPrjSheet($jqgrid_action, $prj['id'], $prj['name']);
				}
			}
		}
		return;
		// cycle 
		// foreach($this->params['id'] as $cycle_id){
			// $this->params['sheets'][] = $this->getCycleSheet($jqgrid_action, $cycle_id);
		// }
		// project
		if($this->cycleCount > 0){
			foreach($this->baseData['prj'] as $prj_id=>$prj){
				$this->params['sheets'][] = $this->getPrjSheet($jqgrid_action, $prj_id, $prj['name']);
			}
		}
// print_r($this->params['sheets']);
	}
	
	protected function setLinuxBSPOptions($jqgrid_action){
	
	}
	
	// 由于生成的Sheet很多，需要一个Cover来管理跳转
	protected function getCoverSheet($jqgrid_action){
		$sheet = array('title'=>'Cover', 'startRow'=>3, 'startCol'=>1, 'pre_img'=>array('img'=>APPLICATION_PATH.'/../public/img/freescale_logo.gif', 'height'=>60, 'width'=>162));
		$row0 = array('no'=>array('index'=>'no', 'label'=>'No.'), 'sheet_name'=>array('index'=>'sheet_name', 'label'=>'Sheet Name'), 'description', 'total_case'=>array('label'=>'Number of Testcases'), 'pass', 'fail', 'pass_rate');
		$sheet['header']['rows'][0] = $row0;
		$sheet['data'] = $this->getCoverData();
		return $sheet;
	}
	
	// 由于生成的Sheet很多，需要一个Cover来管理跳转
	protected function getCycleSummarySheet($jqgrid_action){
		$sheet = array('title'=>'Cycle_Summary', 'startRow'=>4, 'startCol'=>1, 'pre_img'=>array('img'=>APPLICATION_PATH.'/../public/img/freescale_logo.gif', 'height'=>60, 'width'=>162));
		$row0 = array(
			array('index'=>'name', 'label'=>'Cycle'), 
			array('index'=>'rel', 'label'=>'Release'),
			array('index'=>'prj', 'label'=>'Project'), 
			array('index'=>'compiler', 'label'=>'Compiler'),
			array('index'=>'build_target', 'label'=>'Build Target'),
			array('index'=>'test_env', 'label'=>'Test Env'),
			array('index'=>'module', 'label'=>'Module'),
			array('index'=>'total', 'label'=>'Plan TCs'),
			array('index'=>'pass', 'label'=>'Pass'),
			array('index'=>'fail', 'label'=>'Fail'),
			array('index'=>'n/s', 'label'=>'N/S'),
			array('index'=>'n/t', 'label'=>'N/T'),
			array('index'=>'n/a', 'label'=>'N/A'),
			array('index'=>'finish', 'label'=>'Finished', 'hidden'=>true),
			array('index'=>'ne', 'label'=>'Not Finished'),
			array('index'=>'others', 'label'=>'Others'),
			array('index'=>'pass_rate', 'label'=>'Pass Rate', 'style'=>'percent'),
			array('index'=>'finish_rate', 'label'=>'Finish Rate', 'style'=>'percent'),
			array('index'=>'comment', 'label'=>'Comment')
		);
		$sheet['header']['rows'][0] = $row0;
		$sheet['data'] = $this->getCycleSummaryData($this->prjMerge);
		$subTotal = array('locate'=>'module', 'fields'=>array('total', 'pass', 'fail', 'n/a', 'n/t', 'n/s', 'finish', 'ne', 'others'));
		$sheet['groups'] = array(
			array('index'=>'name', 'subtotal'=>array()), 
			array('index'=>'rel', 'subtotal'=>array()),
			array('index'=>'prj', 'subtotal'=>array()),
			array('index'=>'compiler', 'subtotal'=>array()),
			array('index'=>'build_target', 'subtotal'=>array()),
			array('index'=>'test_env', 'subtotal'=>$subTotal),
		);
		if(count($this->test_env) == 1){
			$sheet['header']['rows'][0][5]['hidden'] = true;
		}
		if(count($this->build_target) == 1){
			$sheet['header']['rows'][0][4]['hidden'] = true;
		}
		if(count($this->compiler) == 1){
			$sheet['header']['rows'][0][3]['hidden'] = true;
		}
		return $sheet;
	}
	
	protected function getCodecStreamDetailMidData($cycle_id, $merged = false, $bigCycle = false){
		$mid = array();
		if($bigCycle){
			$mainFields = "detail.prj_id, detail.compiler_id, detail.build_target_id, prj.name as prj, codec_stream.codec_stream_format_id, ".
				" codec_stream_format.name as testcase_module, detail.codec_stream_id, detail.test_env_id, codec_stream.code as testcase, codec_stream.name as summary,".
				" GROUP_CONCAT(CONCAT(detail.testcase_id, ':', detail.result_type_id)) AS results, GROUP_CONCAT(DISTINCT detail.defect_ids) AS cr";
			if(!$merged)
				$mainFields .= ", detail.cycle_id";
			
			$subSql = "cycle_detail";
			if($merged){
				$subSql = "(SELECT cycle_detail.cycle_id, cycle_detail.prj_id, cycle_detail.compiler_id, cycle_detail.build_target_id, cycle_detail.result_type_id, ".
					" cycle_detail.test_env_id, cycle_detail.codec_stream_id, cycle_detail.testcase_id, cycle_detail.defect_ids ".
					" FROM cycle_detail ".
					" WHERE cycle_detail.cycle_id in ($cycle_id) AND codec_stream_id>0 AND ".
					" (prj_id, compiler_id, build_target_id, codec_stream_id, testcase_id, cycle_detail.test_env_id, IFNULL(finish_time, 0)) IN (".
					" SELECT prj_id, compiler_id, build_target_id, codec_stream_id, testcase_id, cycle_detail.test_env_id, MAX(IFNULL(finish_time, 0)) ".
					" FROM cycle_detail ".
					" where cycle_id IN ($cycle_id) AND codec_stream_id>0 AND cycle_detail.result_type_id!=3". // not NT
					" GROUP BY prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, codec_stream_id, testcase_id))";
			}
			$sql = "SELECT $mainFields".
				" FROM $subSql detail ".
				" left join codec_stream on detail.codec_stream_id=codec_stream.id".
				" left join codec_stream_format on codec_stream.codec_stream_format_id=codec_stream_format.id".
				" left join prj on detail.prj_id=prj.id".
				" where detail.cycle_id in ($cycle_id) and detail.codec_stream_id>0 ".
				" GROUP BY detail.cycle_id, detail.prj_id, detail.compiler_id, detail.build_target_id, detail.test_env_id, detail.codec_stream_id".
				" ORDER BY testcase_module, codec_stream_id, cycle_id, prj, test_env_id";
// print_r($sql);
		}
		else{
			$mainFields = "cycle.prj_id, cycle.compiler_id, cycle.build_target_id, prj.name as prj, codec_stream.codec_stream_format_id, ".
				" codec_stream_format.name as testcase_module, detail.codec_stream_id, detail.test_env_id, codec_stream.code as testcase, codec_stream.name as summary,".
				" GROUP_CONCAT(CONCAT(detail.testcase_id, ':', detail.result_type_id)) AS results, GROUP_CONCAT(detail.defect_ids) AS cr";
			if(!$merged)
				$mainFields .= ", detail.cycle_id";
				
			$subSql = "cycle_detail";
			if($merged){
				$subSql = "(SELECT cycle_detail.cycle_id, cycle.prj_id, cycle.compiler_id, cycle.build_target_id, cycle_detail.result_type_id, ".
					" cycle_detail.test_env_id, cycle_detail.codec_stream_id, cycle_detail.testcase_id, cycle_detail.defect_ids ".
					" FROM cycle_detail ".
					" left join cycle on cycle_detail.cycle_id=cycle.id".
					" WHERE cycle_id in ($cycle_id) AND codec_stream_id>0 AND ".
					" (prj_id, compiler_id, build_target_id, codec_stream_id, testcase_id, cycle_detail.test_env_id, IFNULL(finish_time, 0)) IN (".
					" SELECT prj_id, compiler_id, build_target_id, codec_stream_id, testcase_id, cycle_detail.test_env_id, MAX(IFNULL(finish_time, 0)) ".
					" FROM cycle_detail ".
					" left join cycle on cycle_detail.cycle_id=cycle.id".
					" where cycle_id IN ($cycle_id) AND codec_stream_id>0 AND cycle_detail.result_type_id!=3". // not NT
					" GROUP BY prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, codec_stream_id, testcase_id))";
			}
			$sql = "SELECT $mainFields".
				" FROM $subSql detail ".
				" left join cycle on cycle.id=detail.cycle_id".
				" left join codec_stream on detail.codec_stream_id=codec_stream.id".
				" left join codec_stream_format on codec_stream.codec_stream_format_id=codec_stream_format.id".
				" left join prj on cycle.prj_id=prj.id".
				" where detail.cycle_id in ($cycle_id) and detail.codec_stream_id>0 ".
				" GROUP BY detail.cycle_id, cycle.prj_id, cycle.compiler_id, cycle.build_target_id, detail.test_env_id, detail.codec_stream_id".
				" ORDER BY testcase_module, codec_stream_id, cycle_id, prj, test_env_id";
		}
// if($merged)
// print_r($sql);
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			if($merged)
				$row['cycle_id'] = 'merged'; 
			$results = explode(',', $row['results']);
			$stream_result = 0;//RESULT_TYPE_PASS;
			foreach($results as $result){
				$case_result = explode(':', $result);
				if($case_result[1] == RESULT_TYPE_FAIL){
					$stream_result = RESULT_TYPE_FAIL;
					break;
				}
				elseif($case_result[1] != 0){
					if($case_result[1] != RESULT_TYPE_PASS){
						$stream_result = -1; // other
					}
					elseif($stream_result == 0){ // pass
						$stream_result = RESULT_TYPE_PASS;
					}
				}
			}
			$cycle = $this->cycleInfo[$row['cycle_id']]['name'];
			$compiler = $this->compiler[$row['compiler_id']]['name'];
			$build_target = $this->build_target[$row['build_target_id']]['name'];
			$test_env = $this->test_env[$row['test_env_id']]['name'];
			$test_result = array('cycle_id'=>$row['cycle_id'], 'result'=>$stream_result, 'cr'=>$row['cr'], 'summary'=>$row['summary']);
			$mid['cycle'][$cycle][$row['prj']][$compiler][$build_target][$test_env][$row['testcase_module']][$row['testcase']] = $test_result;
			$mid['prj'][$row['prj']][$compiler][$build_target][$test_env][$row['testcase_module']][$cycle][$row['testcase']] = $test_result;
			$mid['module'][$row['testcase_module']][$row['prj']][$compiler][$build_target][$test_env][$cycle][$row['testcase']] = $test_result;
			$mid['detail'][$row['testcase_module']][$row['testcase']][$row['prj']][$compiler][$build_target][$test_env][$cycle] = $test_result;
		}
// print_r($mid);
		return $mid;
	}
	
	protected function getNomalCaseDetailMidData($cycle_id, $merged = false, $bigCycle = false){
		$mid = array();
		$mid_detail = array();
		if($bigCycle){
			$mainFields = "detail.prj_id, detail.compiler_id, detail.build_target_id, prj.name as prj, testcase.testcase_module_id, testcase.code as testcase, testcase.summary, ".
				" testcase_module.name as testcase_module, detail.test_env_id, detail.testcase_id, detail.result_type_id, detail.test_env_id, detail.defect_ids as cr, detail.build_result_id";
				
			if(!$merged)
				$mainFields .= ", cycle_id";
				
			$subSql = "cycle_detail";
			$subFields = "cycle_id, prj_id, compiler_id, build_target_id, result_type_id, cycle_detail.test_env_id, codec_stream_id, testcase_id, defect_ids, build_result_id";
			
			if($merged){
				$subSql = "(SELECT $subFields".
					" FROM cycle_detail ".
					" WHERE cycle_id in ($cycle_id) AND codec_stream_id=0 AND ".
					" (prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id, IFNULL(finish_time, 0)) IN (".
					" SELECT prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id, MAX(IFNULL(finish_time, 0)) ".
					" FROM cycle_detail".
					" where cycle_id IN ($cycle_id) AND codec_stream_id=0".//" and cycle_detail.result_type_id!=3". // not NT
					" GROUP BY prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id))";
			}
			$sql = "SELECT $mainFields".
				" FROM $subSql detail ".
				" left join testcase on detail.testcase_id=testcase.id".
				" left join testcase_module on testcase.testcase_module_id=testcase_module.id".
				" left join prj on detail.prj_id=prj.id".
				" where detail.cycle_id in ($cycle_id) AND detail.codec_stream_id=0".
				" ORDER BY testcase_module, testcase_id, cycle_id, prj, test_env_id";
		}
		else{
			$mainFields = "cycle.prj_id, cycle.compiler_id, cycle.build_target_id, prj.name as prj, testcase.testcase_module_id, testcase.code as testcase, testcase.summary, ".
				" testcase_module.name as testcase_module, detail.test_env_id, detail.testcase_id, detail.result_type_id, detail.test_env_id, detail.defect_ids as cr, detail.build_result_id";
				
			if(!$merged)
				$mainFields .= ", cycle_id";
				
			$subSql = "cycle_detail";
			$subFields = "cycle_id, prj_id, compiler_id, build_target_id, result_type_id, cycle_detail.test_env_id, codec_stream_id, testcase_id, defect_ids, build_result_id";
			
			if($merged){
				$subSql = "(SELECT $subFields".
					" FROM cycle_detail ".
					" left join cycle on cycle_detail.cycle_id=cycle.id".
					" WHERE cycle_id in ($cycle_id) AND codec_stream_id=0 AND ".
					" (prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id, IFNULL(finish_time, 0)) IN (".
					" SELECT prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id, MAX(IFNULL(finish_time, 0)) ".
					" FROM cycle_detail".
					" left join cycle on cycle_detail.cycle_id=cycle.id".
					" where cycle_id IN ($cycle_id) AND codec_stream_id=0 and cycle_detail.result_type_id!=3". // not NT
					" GROUP BY prj_id, compiler_id, build_target_id, cycle_detail.test_env_id, testcase_id))";
			}
			$sql = "SELECT $mainFields".
				" FROM $subSql detail ".
				" left join cycle on cycle.id=detail.cycle_id".
				" left join testcase on detail.testcase_id=testcase.id".
				" left join testcase_module on testcase.testcase_module_id=testcase_module.id".
				" left join prj on cycle.prj_id=prj.id".
				" where detail.cycle_id in ($cycle_id) AND detail.codec_stream_id=0".
				" ORDER BY testcase_module, testcase_id, cycle_id, prj, test_env_id";
		}
// if($merged)
// print_r($sql);
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			if($merged)
				$row['cycle_id'] = 'merged';
			$cycle = $this->cycleInfo[$row['cycle_id']]['name'];
			$compiler = $this->compiler[$row['compiler_id']]['name'];
			$build_target = $this->build_target[$row['build_target_id']]['name'];
			$test_env = $this->test_env[$row['test_env_id']]['name'];
			$test_result = array('cycle_id'=>$row['cycle_id'], 'result'=>$row['result_type_id'], 'cr'=>$row['cr'], 'build_result_id'=>$row['build_result_id'], 'summary'=>$row['summary']);
			$mid['cycle'][$cycle][$row['prj']][$compiler][$build_target][$test_env][$row['testcase_module']][$row['testcase']] = $test_result;
			$mid['prj'][$row['prj']][$compiler][$build_target][$test_env][$row['testcase_module']][$cycle][$row['testcase']] = $test_result;
			$mid['module'][$row['testcase_module']][$row['prj']][$compiler][$build_target][$test_env][$cycle][$row['testcase']] = $test_result;
			$mid['detail'][$row['testcase_module']][$row['testcase']][$row['prj']][$compiler][$build_target][$test_env][$cycle] = $test_result;
		}
		return $mid;
	}

	protected function handleCycleData($mid){
		if(empty($mid))
			return array();
		$i = 0;
		$data = array();
		foreach($mid as $cycle=>$cycle_data){
			foreach($cycle_data as $prj=>$prj_data){
				foreach($prj_data as $compiler=>$compiler_data){
					foreach($compiler_data as $build_target=>$build_target_data){
						foreach($build_target_data as $test_env=>$test_env_data){
							foreach($test_env_data as $testcase_module=>$module_data){
								$base = array('name'=>$cycle, 'prj'=>$prj, //'rel'=>$this->cycleInfo[$cycle_id]['rel'], 
									'compiler'=>$compiler, 'build_target'=>$build_target,
									'test_env'=>$test_env, 'module'=>$testcase_module, 
									'total'=>0, 'pass'=>0, 'fail'=>0, 'n/a'=>0, 'n/s'=>0, 'n/t'=>0, 'ne'=>0, 'others'=>0);
								foreach($module_data as $testcase=>$test_result){
									$result_type_id = $test_result['result'];
									if(is_array($result_type_id))$result_type_id = $result_type_id[0];
									$cr = $test_result['cr'];
									if(is_array($cr))$cr = $cr[0];
									$cycle_id = $test_result['cycle_id'];
									if(is_array($cycle_id))$cycle_id = $cycle_id[0];
// print_r($test_result);
// print_r("result_type_id=$result_type_id\n");									
// if(!isset($this->result_type[$result_type_id]['name'])){
	// print_r("result_type_id = $result_type_id\n");
	// $result_type = 'others';
// }							
// else		
									$result_type = strtolower($this->result_type[$result_type_id]['name']);
									if(!isset($data[$i])){
										$data[$i] = $base;
										$data[$i]['rel'] = $this->cycleInfo[$cycle_id]['rel'];
									}
									$data[$i]['total'] ++;
									if(!in_array($result_type, array('pass', 'fail', 'n/s', 'n/t', 'n/a', 'ne')))
										$data[$i]['others'] ++;
									else
										$data[$i][$result_type] ++;
								}
								$data[$i]['finish'] = $data[$i]['total'] - $data[$i]['ne'];
								$data[$i]['finish_rate'] = $data[$i]['finish'] / $data[$i]['total'];
								$data[$i]['pass_rate'] = $data[$i]['pass'] / $data[$i]['total'];
								$i ++;
							}
						}
					}
				}
			}
		}
		return $data;
	}
	
	protected function getCycleSummaryData(){
		$mid = $this->mid['cycle'];
// print_r($mid);
		$data = $this->handleCycleData($mid);
// print_r($data);
		return $data;
	}
	
	protected function getPrjCompilerModuleSheet($jqgrid_action){
		$sheet = array('title'=>'Project_Module', 'startRow'=>2, 'startCol'=>1);
		
		$row = array('prj', 'compiler', 'build_target', 'test_env', 'module');
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_STATIC_COLUMNS);

		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'build_target'=>array(2, 3), 'test_env'=>array(2, 3), 'module'=>array(2, 3));
		
		$sheet['data'] = $this->getPrjModuleData();
		$sheet['groups'] = array(
			array('index'=>'prj', 'subtotal'=>array()), 
			array('index'=>'compiler', 'subtotal'=>array()), 
			array('index'=>'build_target', 'subtotal'=>array()),
			array('index'=>'test_env', 'subtotal'=>array('locate'=>'module', 'fields'=>$subtotalFields))
		);
		if(count($this->test_env) == 1){
			$sheet['header']['rows'][0][3]['hidden'] = true;
		}
		if(count($this->build_target) == 1){
			$sheet['header']['rows'][0][2]['hidden'] = true;
		}
		if(count($this->compiler) == 1){
			$sheet['header']['rows'][0][1]['hidden'] = true;
		}
		
		return $sheet;
	}
	
	protected function getPrjModuleData(){
		$mid = $this->mid['prj'];
		if(empty($mid))
			return array();
		$data = array();
// print_r($mid);		
		foreach($mid as $prj=>$prj_data){
			foreach($prj_data as $compiler=>$compiler_data){
				foreach($compiler_data as $build_target=>$build_target_data){
					foreach($build_target_data as $test_env=>$test_env_data){
						foreach($test_env_data as $testcase_module=>$module_data){
							$id = $prj.'_'.$compiler.'_'.$build_target.'_'.$test_env.'_'.$testcase_module;
							$data[$id] = array('prj'=>$prj,	'compiler'=>$compiler, 'build_target'=>$build_target, 'test_env'=>$test_env, 'module'=>$testcase_module);
							foreach($module_data as $cycle=>$cycle_data){
								foreach($cycle_data as $testcase=>$test_result){
									$result_type_id = $test_result['result'];
									if(is_array($result_type_id))$result_type_id = $result_type_id[0];
									$cycle_id = $test_result['cycle_id'];
									if(is_array($cycle_id))$cycle_id = $cycle_id[0];
									foreach($this->result_header as $r){
// if(is_array($r)){
	// print_r("r is an array:");
	// print_r($r);
// }
// if(is_array($cycle_id)){
	// print_r("cycle_id is an array:");
	// print_r($cycle_data);
// }
										if(!isset($data[$id]['cycle_'.$cycle_id.'_'.$r]))
											$data[$id]['cycle_'.$cycle_id.'_'.$r] = 0;
									}
									$result_type = strtolower($this->result_type[$result_type_id]['name']);
									if(!in_array($result_type, array('pass', 'fail', 'n/s', 'n/t', 'n/a', 'ne')))
										$result_type = 'others';
									$data[$id]['cycle_'.$cycle_id.'_total'] ++;
									$data[$id]['cycle_'.$cycle_id.'_'.$result_type] ++;
								}
								$data[$id]['cycle_'.$cycle_id.'_finish'] = $data[$id]['cycle_'.$cycle_id.'_total'] - $data[$id]['cycle_'.$cycle_id.'_ne'];
								$data[$id]['cycle_'.$cycle_id.'_finish_rate'] = empty($data[$id]['cycle_'.$cycle_id.'_total']) ? 0 : $data[$id]['cycle_'.$cycle_id.'_finish'] / $data[$id]['cycle_'.$cycle_id.'_total'];
								$data[$id]['cycle_'.$cycle_id.'_pass_rate'] = empty($data[$id]['cycle_'.$cycle_id.'_total']) ? 0 : $data[$id]['cycle_'.$cycle_id.'_pass'] / $data[$id]['cycle_'.$cycle_id.'_total'];
							}
						}
					}
				}
			}
		}
		return $data;
	}
	
	protected function getCoverageSheet($jqgrid_action){
		$sheet = array('title'=>'Testcase Coverage', 'startRow'=>2, 'startCol'=>1, 'pre_text'=>"From {$this->params['coverage_begin']} to {$this->params['coverage_end']}");
		$row0 = array('prj', 'releases', 'cycles', 'testcase_type', 'testcase_module', 'p_1_3', 'p1', 'p2', 'p3');
		$row1 = array('prj', 'releases', 'cycles', 'testcase_type', 'testcase_module', 'total_1_3', 'runed_1_3', 'coverage_1_3', 'total_1', 'runed_1', 'coverage_1', 'total_2', 'runed_2', 'coverage_2', 'total_3', 'runed_3', 'coverage_3');
		$subtotalFields = array('total_1_3', 'runed_1_3', 'coverage_1_3', 'total_1', 'runed_1', 'coverage_1', 'total_2', 'runed_2', 'coverage_2', 'total_3', 'runed_3', 'coverage_3');
		$sheet['header']['rows'] = $this->row2header(array($row0, $row1));
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'testcase_type'=>array(2, 3), 'testcase_module'=>array(2, 3), 'releases'=>array(2, 3), 'cycles'=>array(2, 3));
//print_r($sheet['header']);
		$sheet['data'] = $this->getCoverageData($this->params['coverage_begin'], $this->params['coverage_end']);
		$sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), array('index'=>'releases', 'subtotal'=>array()), array('index'=>'cycles', 'subtotal'=>array()), array('index'=>'testcase_type', 'subtotal'=>array('locate'=>'testcase_module', 'fields'=>$subtotalFields)));
//print_r($sheet);
		return $sheet;
	}
	
	protected function getModulePrjCompilerSheet($jqgrid_action){
		$sheet = array('title'=>'Module', 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'prj', 'compiler', 'build_target', 'test_env');
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_STATIC_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'module'=>array(2, 3), 'build_target'=>array(2, 3), 'test_env'=>array(2, 3));
		$sheet['data'] = $this->getModulePrjData();
		$sheet['groups'] = array(
			array('index'=>'module'),// 'subtotal'=>array('locate'=>'test_env', 'fields'=>$subtotalFields)), 
			array('index'=>'prj'), 
			array('index'=>'compiler'),
			array('index'=>'build_target'),
			array('index'=>'test_env'),
		);
		$sheet['last_total'] = array(
			'locate'=>'module',
			'fields'=>$subtotalFields,
		);
		
		if(count($this->test_env) == 1){
			$sheet['header']['rows'][0][4]['hidden'] = true;
		}
		if(count($this->build_target) == 1){
			$sheet['header']['rows'][0][3]['hidden'] = true;
		}
		if(count($this->compiler) == 1){
			$sheet['header']['rows'][0][2]['hidden'] = true;
		}
		return $sheet;
	}
	
	protected function getModulePrjData(){
		$mid = $this->mid['module'];
		if(empty($mid))
			return array();
		$data = array();
			// $mid['module'][$row['testcase_module']][$row['prj']][$compiler][$build_target][$test_env][$cycle][$row['testcase']] = $test_result;
		foreach($mid as $testcase_module=>$module_data){
			foreach($module_data as $prj=>$prj_data){
				foreach($prj_data as $compiler=>$compiler_data){
					foreach($compiler_data as $build_target=>$build_target_data){
						foreach($build_target_data as $test_env=>$test_env_data){
							$id = $testcase_module.'_'.$prj.'_'.$compiler.'_'.$build_target.'_'.$test_env;
							$data[$id] = array('prj'=>$prj,	'compiler'=>$compiler, 'build_target'=>$build_target, 'test_env'=>$test_env, 'module'=>$testcase_module);
							foreach($test_env_data as $cycle=>$cycle_data){
								foreach($cycle_data as $testcase=>$test_result){
									$result_type_id = $test_result['result'];
									if(is_array($result_type_id))$result_type_id = $result_type_id[0];
									$cycle_id = $test_result['cycle_id'];
									if(is_array($cycle_id))$cycle_id = $cycle_id[0];
									foreach($this->result_header as $r){
										if(!isset($data[$id]['cycle_'.$cycle_id.'_'.$r]))
											$data[$id]['cycle_'.$cycle_id.'_'.$r] = 0;
									}
									$result_type = strtolower($this->result_type[$result_type_id]['name']);
									if(!in_array($result_type, array('pass', 'fail', 'n/s', 'n/t', 'n/a', 'ne')))
										$result_type = 'others';
									$data[$id]['cycle_'.$cycle_id.'_total'] ++;
									$data[$id]['cycle_'.$cycle_id.'_'.$result_type] ++;
								}
								$data[$id]['cycle_'.$cycle_id.'_finish'] = $data[$id]['cycle_'.$cycle_id.'_total'] - $data[$id]['cycle_'.$cycle_id.'_ne'];
								$data[$id]['cycle_'.$cycle_id.'_finish_rate'] = empty($data[$id]['cycle_'.$cycle_id.'_total']) ? 0 : $data[$id]['cycle_'.$cycle_id.'_finish'] / $data[$id]['cycle_'.$cycle_id.'_total'];
								$data[$id]['cycle_'.$cycle_id.'_pass_rate'] = empty($data[$id]['cycle_'.$cycle_id.'_total']) ? 0 : $data[$id]['cycle_'.$cycle_id.'_pass'] / $data[$id]['cycle_'.$cycle_id.'_total'];
							}
						}
					}
				}
			}
		}
		return $data;
	}
		
	protected function getDetailSheet($jqgrid_action){
		$sheet = array('title'=>'Detail', 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'testcase', 'summary'=>array('hidden'=>true, 'label'=>'Title'), /*'expected_result'=>array('hidden'=>true),*/ 'prj', 'compiler'=>array('hidden'=>true), 
			'build_target'=>array('hidden'=>true), 'test_env'=>array('hidden'=>true));
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_DETAIL_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array(
			'module'=>array(2, 3), 'testcase'=>array(2, 3), 'summary'=>array(2, 3), /*'expected_result'=>array(2, 3),*/ 'prj'=>array(2, 3), 
			'compiler'=>array(2, 3), 'build_target'=>array(2, 3), 'test_env'=>array(2, 3)
		);
		$sheet['data'] = $this->getDetailData();
		$sheet['groups'] = array(
			array('index'=>'module', 'subtotal'=>array('locate'=>'testcase', 'label'=>'Click + for detail', 'fields'=>array())),  
			array('index'=>'testcase'), 
			array('index'=>'summary'),
			// array('index'=>'expected_result'), 
			array('index'=>'prj'), 
			array('index'=>'compiler'), 
			array('index'=>'build_target'), 
			array('index'=>'test_env')
		);
// print_r($sheet);
		return $sheet;
	}

	protected function getDetailData(){
		$mid = $this->mid['detail'];
		if(empty($mid))
			return array();
		$data = array();
			// $mid['detail'][$row['testcase_module']][$row['testcase']][$row['prj']][$compiler][$build_target][$test_env][$cycle] = $test_result;
		foreach($mid as $testcase_module=>$module_data){
			foreach($module_data as $testcase=>$testcase_data){
				foreach($testcase_data as $prj=>$prj_data){
					foreach($prj_data as $compiler=>$compiler_data){
						foreach($compiler_data as $build_target=>$build_target_data){
							foreach($build_target_data as $test_env=>$test_env_data){
								$id = $testcase_module.'_'.$prj.'_'.$compiler.'_'.$build_target.'_'.$test_env.'_'.$testcase;
								$data[$id] = array('prj'=>$prj,	'compiler'=>$compiler, 'build_target'=>$build_target, 'test_env'=>$test_env, 'module'=>$testcase_module, 'testcase'=>$testcase);
								foreach($test_env_data as $cycle=>$test_result){
									$result_type_id = $test_result['result'];
									if(is_array($result_type_id))$result_type_id = $result_type_id[0];
									$build_result_id = empty($test_result['build_result_id']) ? RESULT_TYPE_PASS : $test_result['build_result_id'];
									if(is_array($build_result_id))$build_result_id = $build_result_id[0];
									$cycle_id = $test_result['cycle_id'];
									if(is_array($cycle_id))$cycle_id = $cycle_id[0];
									$result_type = $this->result_type[$result_type_id]['name'];
									$build_result = $this->result_type[$build_result_id]['name'];
									$summary = $test_result['summary'];
									if(is_array($summary))$summary = $summary[0];
									if($result_type == 'ne')
										$result_type = 'Not Finished';
									$data[$id]['cycle_'.$cycle_id.'_result_type'] = $result_type;
									$data[$id]['cycle_'.$cycle_id.'_build_result'] = $build_result;
									$data[$id]['cycle_'.$cycle_id.'_cr'] = $test_result['cr'];
									$data[$id]['summary'] = $summary;
								}
							}
						}
					}
				}
			}
		}
		return $data;
	}

	protected function getCycleSheet($jqgrid_action, $cycle_id){
		$sheet = array('title'=>$this->cycleInfo[$cycle_id]['name'], 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'codec_stream', 'testcase', 'expected_result'=>array('hidden'=>true), 'test_env'=>array('hidden'=>true));
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_DETAIL_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('testcase'=>array(2, 3), 'codec_stream'=>array(2,3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 'test_env'=>array(2, 3));
		$sheet['data'] = $this->getCycleData($cycle_id);
		$sheet['groups'] = array(array('index'=>'module'), array('index'=>'codec_stream'), array('index'=>'testcase'), array('index'=>'expected_result'),
			array('index'=>'test_env'));
		return $sheet;
	}
	
	protected function getPrjSheet($jqgrid_action, $prj_id, $prj_name){
		$sheet = array('title'=>$prj_name, 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'codec_stream', 'testcase', 'expected_result'=>array('hidden'=>true), 'compiler'=>array('hidden'=>true), 
			'build_target'=>array('hidden'=>true), 'test_env'=>array('hidden'=>true));
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_DETAIL_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('testcase'=>array(2, 3), 'codec_stream'=>array(2,3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 
			'compiler'=>array(2, 3), 'build_target'=>array(2, 3), 'test_env'=>array(2, 3)
		);
		$sheet['data'] = $this->getPrjData($prj_id);
		$sheet['groups'] = array(array('index'=>'module'), array('index'=>'codec_stream'), array('index'=>'testcase'), array('index'=>'expected_result'),
			array('index'=>'compiler'), array('index'=>'build_target'), array('index'=>'test_env'));
//		$sheet['groups'] = array('code'=>array(), 'module'=>array());
		return $sheet;
	}
	
	protected function row2header($rows, $flag = WITH_RESULT_STATIC_COLUMNS){
		$header = array();
		$subtotal = array();
		$col = 0;
		foreach($rows as $i=>$row){
			foreach($row as $field=>$prop){
				$header[$i][] = $this->getFieldHeader($field, $prop);
				$col ++;
			}
		}
		switch($flag){
			case WITH_RESULT_STATIC_COLUMNS:
				$hiddenFields = array('n/a', 'n/s', 'n/t', 'others', 'finish');
				foreach($this->result_header as $field=>$prop){
					$h = $this->getFieldHeader($field, $prop);
					$h['cols'] = $this->cycleCount;
					if($this->prjMerge == 1)
						$h['cols'] ++;
					if(in_array($prop, $hiddenFields))
						$h['hidden'] = true;
					$header[0][] = $h;
					
					foreach($this->params['id'] as $cycle_id){
						$h = $this->getFieldHeader($field, $prop);
						$h['index'] = 'cycle_'.$cycle_id.'_'.$prop;
						$h['label'] = $this->cycleInfo[$cycle_id]['name'];
						// $h['hidden'] = $this->prjMerge;
						if($prop == 'pass_rate' || $prop == 'finish_rate')
							$h['style'] = 'percent';
						else
							$subtotal[] = $h['index'];
						if(in_array($prop, $hiddenFields))
							$h['hidden'] = true;
						$header[1][] = $h;
						$col ++;
					}
					if($this->prjMerge == 1){
						$h = array('label'=>'Final', 'index'=>'cycle_merged_'.$prop);
						if($prop == 'pass_rate' || $prop == 'finish_rate')
							$h['style'] = 'percent';
						else
							$subtotal[] = $h['index'];
						if(in_array($prop, $hiddenFields))
							$h['hidden'] = true;
						$header[1][] = $h;
					}
				}
				break;
			
			case WITH_RESULT_STATIC_COLUMNS2:
				$hiddenFields = array('n/a', 'n/s', 'n/t', 'others', 'finish');
				$resultCount = count($this->result_header);
				foreach($this->cycleInfo as $cycle_id=>$cycleInfo){
					$h = array('label' => $cycleInfo['name'], 'cols'=>$resultCount);
					$header[0][] = $h;
					foreach($this->result_header as $field=>$prop){
						$h = $this->getFieldHeader($field, $prop);
						$h['index'] = 'cycle_'.$cycle_id.'_'.$prop;
						if($prop == 'pass_rate' || $prop == 'finish_rate')
							$h['style'] = 'percent';
						else
							$subtotal[] = $h['index'];
						if(in_array($prop, $hiddenFields))
							$h['hidden'] = true;
						$header[1][] = $h;
						$col ++;
					}
				}
				break;
			case WITH_RESULT_DETAIL_COLUMNS:
				$detail_results = array('build_result'=>'Build Result', 'result_type'=>'Test Result', 'cr'=>'CR');
				$columns = count($this->params['id']);
				if($this->prjMerge)
					$columns ++;
				foreach($detail_results as $k=>$r){
					$header[0][] = array('label'=>$r, 'cols'=>$columns, 'hidden'=>$k == 'build_result');
					foreach($this->params['id'] as $cycle_id){
						$header[1][] = array('label'=>$this->cycleInfo[$cycle_id]['name'], 'index'=>'cycle_'.$cycle_id.'_'.$k, 'width'=>100, 'hidden'=>$k == 'build_result');
					}
					if($this->prjMerge == 1){
						$header[1][] = array('index'=>'cycle_merged_'.$k, 'label'=>'Final', 'hidden'=>$k == 'build_result');
					}
				}
				break;
			case WITH_COVERAGE_COLUMNS:
				break;
		}
		
		return array($header, $subtotal);
	}
	
	protected function getFieldHeader($field, $prop){
		if (is_int($field))
			$field = $prop;
		if (is_string($prop))
			$prop = array('index'=>$prop);
		if (empty($prop['index']))
			$prop['index'] = $field;
		switch($prop['index']){
			case 'prj':
				$prop['label'] = 'Project';
				$prop['width'] = 150;
				break;
			case 'pass_rate':
			case 'finish_rate':
				$prop['label'] = 'Pass Rate';
				if($prop['index'] == 'finish_rate')
					$prop['label'] = 'Finish Rate';
				$prop['style'] = 'percent';
				$prop['width'] = 150;
				break;
			case 'result_type':
			case 'build_result':
				$prop['label'] = 'Result';
				if ($prop['index'] == 'build_result'){
					$prop['label'] = 'Build Result';
					$prop['hidden'] = true;
				}
				break;
			case 'code':
				$prop['label'] = 'Testcase';
				break;
			case 'n/a':
				$prop['label'] = 'N/A';
				break;
			case 'n/s':
				$prop['label'] = 'N/S';
				break;
			case 'n/t':
				$prop['label'] = 'N/T';
				break;
			case 'ne':
				$prop['label'] = 'Not Finished';
				break;
			case 'others':
				$prop['label'] = 'Others';
				break;
		}
		if (empty($prop['label']))
			$prop['label'] = ucwords($prop['index']);
		if (empty($prop['width']))
			$prop['width'] = 100;
		if (empty($prop['cols']))
			$prop['cols'] = 1;
// print_r($prop);			
		return $prop;
	}
	
	protected function getCoverageData($begin_date, $end_date){
		$data = array();
		$str_prj = implode(',', $this->params['id']);
		$sql = "select link.prj_id, tc.testcase_type_id, tc.testcase_module_id, link.testcase_priority_id, count(*) as cc".
			" FROM prj_testcase_ver link left join testcase tc on link.testcase_id=tc.id".
			" where link.edit_status_id in (1, 2) and link.prj_id in ($str_prj) AND link.testcase_priority_id<4 and tc.isactive=".ISACTIVE_ACTIVE.
			" group by link.prj_id, tc.testcase_type_id, tc.testcase_module_id, link.testcase_priority_id";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			if (empty($data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_1_3'])){
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_1_3'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_1'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_2'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_3'] = 0;
			}
			$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_1_3'] += $row['cc'];
			$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['total_'.$row['testcase_priority_id']] = $row['cc'];
		}
		
		$skipResult = implode(',', array(RESULT_TYPE_SKIP, RESULT_TYPE_NT));
		$sql = "select last.prj_id, tc.testcase_type_id, tc.testcase_module_id, ver.testcase_priority_id, count(*) as cc".
			" FROM testcase_last_result last left join prj_testcase_ver link on last.prj_id=link.prj_id and last.testcase_id=link.testcase_id".
			" left join testcase tc on last.testcase_id=tc.id".
			" left join cycle_detail on last.cycle_detail_id=cycle_detail.id".
			" left join testcase_ver ver on cycle_detail.testcase_ver_id=ver.id".
			" where last.prj_id in ($str_prj) and ver.testcase_priority_id<4 and last.result_type_id NOT in ($skipResult)".
			" and last.tested>='$begin_date' and last.tested<='$end_date' and NOT ISNULL(link.id)".
			" group by last.prj_id, tc.testcase_type_id, tc.testcase_module_id, ver.testcase_priority_id";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			if (empty($data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_1_3'])){
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_1_3'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_1'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_2'] = 0;
				$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_3'] = 0;
			}
			$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_1_3'] += $row['cc'];
			$data[$row['prj_id']][$row['testcase_type_id']][$row['testcase_module_id']]['runed_'.$row['testcase_priority_id']] = $row['cc'];
		}
		$sql = "SELECT last.prj_id, last.rel_id, group_concat(distinct rel.name separator ',') as rel, group_concat(distinct cycle.name separator ',')as cycle ".
			" FROM testcase_last_result last left join cycle_detail on last.cycle_detail_id=cycle_detail.id".
			" left join cycle on cycle_detail.cycle_id=cycle.id".
			" left join rel on last.rel_id=rel.id".
			" where last.prj_id in ($str_prj) and tested>='$begin_date'".
			" GROUP BY rel_id, cycle.id";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$rel[$row['prj_id']] = $row;
		}
//print_r($data);		
		$i = 0;
		$prjs = array();
		$testcase_types = array();
		$testcase_modules = array();
		foreach($data as $prj_id=>$prj_data){
			if (!isset($prjs[$prj_id])){
				$res = $this->db->query("SELECT name from prj where id=$prj_id");
				$prj = $res->fetch();
				$prjs[$prj_id] = $prj['name'];
			}
			foreach($prj_data as $testcase_type_id=>$type_data){
				if (!isset($testcase_types[$testcase_type_id])){
					$res = $this->db->query("select * from testcase_type where id=$testcase_type_id");
					$testcase_type = $res->fetch();
					$testcase_types[$testcase_type_id] = $testcase_type['name'];
				}
				foreach($type_data as $testcase_module_id=>$module_data){
					if (!isset($testcase_modules[$testcase_module_id])){
						$res = $this->db->query("select * from testcase_module where id=$testcase_module_id");
						$testcase_module = $res->fetch();
						$testcase_modules[$testcase_module_id] = $testcase_module['name'];
					}
					$this->data['coverage'][$i] = array_merge(compact('prj_id', 'testcase_type_id', 'testcase_module_id'), $module_data);
					$this->data['coverage'][$i]['releases'] = $rel[$prj_id]['rel'];
					$this->data['coverage'][$i]['cycles'] = $rel[$prj_id]['cycle'];
					$this->data['coverage'][$i]['prj'] = $prjs[$prj_id];
					$this->data['coverage'][$i]['testcase_type'] = $testcase_types[$testcase_type_id];
					$this->data['coverage'][$i]['testcase_module'] = $testcase_modules[$testcase_module_id];
					$this->data['coverage'][$i]['coverage_1_3'] = empty($this->data['coverage'][$i]['total_1_3']) ? 0 : (empty($this->data['coverage'][$i]['runed_1_3']) ? 0 : $this->data['coverage'][$i]['runed_1_3'] / $this->data['coverage'][$i]['total_1_3']);
					$this->data['coverage'][$i]['coverage_1'] =  empty($this->data['coverage'][$i]['total_1']) ? 0 : (empty($this->data['coverage'][$i]['runed_1']) ? 0 : $this->data['coverage'][$i]['runed_1'] / $this->data['coverage'][$i]['total_1']);
					$this->data['coverage'][$i]['coverage_2'] =  empty($this->data['coverage'][$i]['total_2']) ? 0 : (empty($this->data['coverage'][$i]['runed_2']) ? 0 : $this->data['coverage'][$i]['runed_2'] / $this->data['coverage'][$i]['total_2']);
					$this->data['coverage'][$i]['coverage_3'] =  empty($this->data['coverage'][$i]['total_3']) ? 0 : (empty($this->data['coverage'][$i]['runed_3']) ? 0 : $this->data['coverage'][$i]['runed_3'] / $this->data['coverage'][$i]['total_3']);
					$i ++;
				}
			}
		}
		return $this->data['coverage'];
	}
	
	protected function getTotalRow($sheetIndex, $last_total_desc){
		$totalRow = parent::getTotalRow($sheetIndex, $last_total_desc);
		$sheet_title = $this->params['sheets'][$sheetIndex]['title'];
		switch($sheet_title){
			case 'Module':
				foreach($this->cycleInfo as $cycleInfo){
					$totalRow['cycle_'.$cycleInfo['id'].'_pass_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'cycle_'.$cycleInfo['id'].'_pass', 'cycle_'.$cycleInfo['id'].'_total');
					$totalRow['cycle_'.$cycleInfo['id'].'_finish_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'cycle_'.$cycleInfo['id'].'_finish', 'cycle_'.$cycleInfo['id'].'_total');
				}
				break;
				
		}
		return $totalRow;
	}
	
	protected function getSubtotalRow($sheetIndex, $field, $subtotal, $last){
		$subtotalRow = parent::getSubtotalRow($sheetIndex, $field, $subtotal, $last);
		$sheet_title = $this->params['sheets'][$sheetIndex]['title'];
		switch($sheet_title){
			case 'Cycle_Summary':
				$subtotalRow['pass_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'pass', 'total');
				$subtotalRow['finish_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'finish', 'total');
				break;
			case 'Project_Module':
				foreach($this->cycleInfo as $cycleInfo){
					$subtotalRow['cycle_'.$cycleInfo['id'].'_pass_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'cycle_'.$cycleInfo['id'].'_pass', 'cycle_'.$cycleInfo['id'].'_total');
					$subtotalRow['cycle_'.$cycleInfo['id'].'_finish_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'cycle_'.$cycleInfo['id'].'_finish', 'cycle_'.$cycleInfo['id'].'_total');
				}
				break;
				
		}
		
		// if ($sheetIndex == 0 || $sheetIndex == $this->moduleSheet){
			// foreach($subtotal['fields'] as $subfield){
				// if (preg_match('/^(.*?)_pass_rate$/', $subfield, $matches)){
					// $pass = $matches[1].'_pass';
					// $total = $matches[1].'_total';
					// $subtotalRow[$subfield] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], $pass, $total);
				// }
			// }
		// }
		// else if ($this->params['include_coverage'][0] && $sheetIndex == 1){
			// $subtotalRow['coverage_1_3'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_1_3', 'total_1_3');
			// $subtotalRow['coverage_1'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_1', 'total_1');
			// $subtotalRow['coverage_2'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_2', 'total_2');
			// $subtotalRow['coverage_3'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_3', 'total_3');
		// }
		return $subtotalRow;
	}
	
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$v = $content[$headerIndex];
		$style = parent::calcStyle($sheetIndex, $headerIndex, $v, $default);
		if ($sheetIndex > $this->moduleSheet && !empty($v) && (stripos($headerIndex, 'result_type') !== false || stripos($headerIndex, 'build_result') !== false)){
			if (strtolower($v) != 'pass'){//RESULT_TYPE_PASS){
				$style = 'warning';
			}
		}
		return $style;
	}

};
?>
 