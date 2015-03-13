<?php
require_once('exporter_excel.php');
require_once('toolfactory.php');

class xt_testcase_exporter_query_report extends exporter_excel{
	protected $db = null;
	protected $tool = null;
	protected $detail = array();
	protected $lastReslutDetail = array();
	protected $lastReslutByPrj = array();
	protected $lastReslutMid = array();
	public function setOptions($jqgrid_action){
		$this->tool = toolFactory::get('db');
		$this->tool->setDb($this->params['db']);
		
		if(in_array('not_tested', $this->params['report_type'])){
			$this->params['sheets'][] = $this->getNotTestedCasesSheet();
			$this->params['sheets'][] = $this->getNotTestedCasesDetailSheet();
		}
		if(in_array('last_result', $this->params['report_type'])){
			$this->params['sheets'][] = $this->getLastResultSheet();
			$this->params['sheets'][] = $this->getLastResultSheetByPrj();
			$this->params['sheets'][] = $this->getLastResultDetailSheet();
		}
		if(in_array('modified_testcase', $this->params['report_type'])){
			$this->params['sheets'][] = $this->getModifiedTestcaseSheet();
		}
	}
	
	//按每个active的Project，统计Case的执行情况
	protected function getNotTestedCasesSheet(){
		$header = array(
			array(
				array('label'=>'Testcase Type', 'index'=>'testcase_type'),
				array('label'=>'Project', 'index'=>'prj', 'width'=>'150'),
				array('label'=>'Module', 'index'=>'testcase_module'),
				array('label'=>'Primary', 'index'=>'primary'),
				array('label'=>"P1~P3 execute rate[{$this->params['not_tested_from']} to {$this->params['not_tested_to']}]", 'index'=>'execute_rate', 'cols'=>4),
				array('label'=>"P1 execute rate[{$this->params['not_tested_from']} to {$this->params['not_tested_to']}]", 'index'=>'p1_execute_rate', 'cols'=>4),
				array('label'=>'Run Cycles', 'index'=>'cycles'),
				array('label'=>'Comment', 'index'=>'comment'),
			),
			array(
				array('label'=>'Testcase Type', 'index'=>'testcase_type'),
				array('label'=>'Project', 'index'=>'prj', 'width'=>150),
				array('label'=>'Module', 'index'=>'testcase_module'),
				array('label'=>'Primary', 'index'=>'primary'),
				array('label'=>"Not Run", 'index'=>'p1_3_not_run', 'width'=>120),
				array('label'=>"Run Cases", 'index'=>'p1_3_run', 'hidden'=>true, 'width'=>120),
				array('label'=>"Total valid cases", 'index'=>'p1_3_total_cases', 'width'=>120),
				array('label'=>"Execute Rate", 'index'=>'p1_3_exe_rate', 'style'=>'percent'),
				array('label'=>"Not Run", 'index'=>'p1_not_run', 'width'=>120),
				array('label'=>"Run Cases", 'index'=>'p1_run', 'hidden'=>true, 'width'=>120),
				array('label'=>"Total valid cases", 'index'=>'p1_total_cases', 'width'=>120),
				array('label'=>"Execute Rate", 'index'=>'p1_exe_rate', 'style'=>'percent'),
				array('label'=>'Run Cycles', 'index'=>'cycles'),
				array('label'=>'Comment', 'index'=>'comment'),
			),
		);
		$data = $this->getNotTestedData();
		$sheet = array('title'=>'Execute_Rate', 'startRow'=>2, 'startCol'=>1, 
			'pre_text'=>"Case execute coverage from {$this->params['not_tested_from']} to {$this->params['not_tested_to']} (auto+manual)",
			'header'=>array(
				'rows'=>$header, 
				'mergeCols'=>array('testcase_type'=>array(2, 3), 'testcase_module'=>array(2, 3), 'prj'=>array(2, 3), 'primary'=>array(2, 3), 'cycles'=>array(2, 3), 'comment'=>array(2, 3))
			), 
			'data'=>$data, 
			'groups'=>array(
				array('index'=>'testcase_type'),
				array('index'=>'prj', 'subtotal'=>array('locate'=>'testcase_module', 'fields'=>array('p1_3_not_run', 'p1_3_total_cases', 'p1_3_run', 'p1_not_run', 'p1_total_cases', 'p1_run'))), 
			)
		);
// print_r($sheet);
		return $sheet;
	}
	
	protected function getNotTestedData(){
		$data = array();
		$strTestcaseType = '';
		if(!empty($this->params['testcase_type_id'])){
			$strTestcaseType = " AND testcase_type_id IN (".implode(',', $this->params['testcase_type_id']).")";
		}
		$strPrj = '';
		if(!empty($this->params['prj_ids'])){
			$strTestcaseType = " AND prj.id IN (".implode(',', $this->params['prj_ids']).")";
		}
		//查找所有在指定时间内没有测试过的testcases
		$where = " 1 ";
		if(!empty($this->params['not_tested_from']))
			$where .= " AND finish_time>='{$this->params['not_tested_from']}'";
		if(!empty($this->params['not_tested_to']))
			$where .= " AND finish_time<='{$this->params['not_tested_to']}'";
		
		$testedSql = "SELECT testcase_id, max(finish_time) as last_run_time, group_concat(DISTINCT cycle.name SEPARATOR '\n') as cycles ".
			" FROM cycle_detail left join cycle on cycle.id=cycle_detail.cycle_id".
			" WHERE $where".
			" group by testcase_id";
		$notTestedSql = "SELECT prj_testcase_ver.testcase_id, prj_testcase_ver.prj_id, testcase.testcase_module_id, testcase_ver.testcase_priority_id, testcase_ver.ver, ".
			" testcase.code, testcase.testcase_type_id, testcase_type.name as testcase_type, testcase_module.name as testcase_module, ".
			" A.last_run_time, prj.name as prj, prj.isactive as prj_active, testcase.isactive as testcase_active, A.cycles".
			" from prj_testcase_ver left join testcase on testcase.id=prj_testcase_ver.testcase_id".
			" left join ($testedSql) A on prj_testcase_ver.testcase_id=A.testcase_id".
			" left join prj on prj_testcase_ver.prj_id=prj.id".
			" left join testcase_module on testcase.testcase_module_id=testcase_module.id".
			" left join testcase_type on testcase.testcase_type_id=testcase_type.id".
			" left join testcase_ver on prj_testcase_ver.testcase_ver_id=testcase_ver.id".
			" WHERE prj.isactive=".ISACTIVE_ACTIVE." AND testcase.isactive=".ISACTIVE_ACTIVE." AND testcase_ver.testcase_priority_id < 4 $strTestcaseType $strPrj".
			" group by prj_id, testcase_id".
			" order by testcase_type ASC, prj ASC, testcase_module ASC, code ASC";
		
// print_r($notTestedSql);
		$res = $this->tool->query($notTestedSql);
		while($row = $res->fetch()){
			if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_total_cases']))
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_total_cases'] = 0;
			$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_total_cases'] ++;
			if($row['testcase_priority_id'] == 1){
				if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_total_cases']))
					$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_total_cases'] = 0;
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_total_cases'] ++;
			}
			if(is_null($row['last_run_time'])){
				$this->detail[$row['testcase_type']][$row['prj']][$row['code']] = $row;
			
				if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_not_run']))
					$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_not_run'] = 0;
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_3_not_run'] ++;
				if($row['testcase_priority_id'] == 1){
					if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_not_run']))
						$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_not_run'] = 0;
					$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['p1_not_run'] ++;
				}
			}
			if(!empty($row['cycles']))
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['cycles'] = $row['cycles'];
		}
		$ret = array();
		foreach($data as $testcase_type=>$testcase_type_data){
			foreach($testcase_type_data as $prj=>$prj_data){
				foreach($prj_data as $testcase_module=>$module_data){
					$a = array('testcase_type'=>$testcase_type, 'testcase_module'=>$testcase_module, 'prj'=>$prj);
					$a = array_merge($a, $module_data);
					if(empty($a['p1_3_not_run']))
						$a['p1_3_not_run'] = 0;
					$a['p1_3_run'] = $a['p1_3_total_cases'] - $a['p1_3_not_run'];
					$a['p1_3_exe_rate'] = !empty($a['p1_3_not_run']) ? $a['p1_3_run']/$a['p1_3_total_cases'] : 1;
					if(!empty($a['p1_total_cases'])){
						if(empty($a['p1_not_run']))
							$a['p1_not_run'] = 0;
						$a['p1_run'] = $a['p1_total_cases'] - $a['p1_not_run'];
						$a['p1_exe_rate'] = !empty($a['p1_not_run']) ? $a['p1_run']/$a['p1_total_cases'] : 1;
					}
					$ret[] = $a;
				}
			}
		}
// print_r($ret);
		return $ret;
	}
	
	protected function getNotTestedCasesDetailSheet(){
		$header = array(
			array(
				array('label'=>'Testcase Type', 'index'=>'testcase_type', 'width'=>120),
				array('label'=>'Project', 'index'=>'prj', 'width'=>'150'),
				array('label'=>'Module', 'index'=>'testcase_module', 'width'=>200),
				array('label'=>'Testcase', 'index'=>'code', 'width'=>300),
				array('label'=>'Priority', 'index'=>'testcase_priority_id'),
				array('label'=>'Ver', 'index'=>'ver')
			),
		);
		$data = $this->getNotTestedDetailData();
		$sheet = array('title'=>'Not_Run_Cases', 'startRow'=>2, 'startCol'=>1, 
			'pre_text'=>"Not Run Cases from {$this->params['not_tested_from']} to {$this->params['not_tested_to']} (auto+manual)",
			'header'=>array(
				'rows'=>$header, 
			), 
			'data'=>$data, 
			// 'groups'=>array(
				// array('index'=>'testcase_type'),
				// array('index'=>'prj'),
				// array('index'=>'testcase_module'), 
			// )
		);
// print_r($sheet);
		return $sheet;
	}
	
	protected function getNotTestedDetailData(){
		$data = array();
				// $this->detail[$row['testcase_type']][$row['prj']][$row['code']] = $row;
		foreach($this->detail as $testcase_type=>$testcase_type_data){
			foreach($testcase_type_data as $prj=>$prj_data){
				foreach($prj_data as $code=>$code_data){
					$testcase_priority_id = $code_data['testcase_priority_id'];
					$ver = $code_data['ver'];
					$testcase_module = $code_data['testcase_module'];
					$data[] = compact('testcase_type', 'prj', 'testcase_module', 'code', 'testcase_priority_id', 'ver');
				}
			}
		}
		return $data;
	}
	
	//按每个active的Project，统计Case的LastResult情况
	protected function getLastResultSheet(){
		$header = array(
			array(
				array('label'=>'Testcase Type', 'index'=>'testcase_type'),
				array('label'=>'Project', 'index'=>'prj', 'width'=>'150'),
				array('label'=>'Module', 'index'=>'testcase_module'),
				array('label'=>'Total Valid Cases', 'index'=>'total_cases'),
				array('label'=>'Total Tested', 'index'=>'total_tested'),
				array('label'=>'Pass', 'index'=>'pass'),
				array('label'=>'Fail', 'index'=>'fail'),
				array('label'=>'NS', 'index'=>'ns'),
				array('label'=>'Timeout', 'index'=>'timeout'),
				
				// array('label'=>'NT', 'index'=>'nt'),
				// array('label'=>'NA', 'index'=>'na'),
				// array('label'=>'Others', 'index'=>'others'),
				array('label'=>'Pass Rate', 'index'=>'pass_rate', 'style'=>'percent'),
			),
		);
		$data = $this->getLastResultData();
		$sheet = array('title'=>'Last_Result', 'startRow'=>2, 'startCol'=>1, 
			'pre_text'=>"Last Result From {$this->params['last_result_from']} to {$this->params['last_result_to']}",
			'header'=>array(
				'rows'=>$header, 
			), 
			'data'=>$data, 
			'groups'=>array(
				array('index'=>'testcase_type'),
				array('index'=>'prj', 'subtotal'=>array('locate'=>'testcase_module', 'fields'=>array('total_cases', 'total_tested', 'pass', 'fail', 'ns', 'timeout'))), 
			)
		);
// print_r($sheet);
		return $sheet;
	}
	
	protected function getLastResultData(){
		$mid = array();
		$data = array();
		$normalSql = $this->getNormalCaseLastResultSql();
		$streamSql = $this->getStreamCaseLastResultSql();
		$sql = "($normalSql) UNION ($streamSql)";
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$result_type = 'others';
			switch($row['result_type_id']){
				case RESULT_TYPE_PASS:
					$result_type = 'pass';
					break;
				case RESULT_TYPE_FAIL:
					$result_type = 'fail';
					break;
				case RESULT_TYPE_NS:
					$result_type = 'ns';
					break;
				case RESULT_TYPE_TIMEOUT:
					$result_type = 'timeout';
					break;
				case RESULT_TYPE_NA:
					$result_type = 'na';
					break;
				case RESULT_TYPE_NT:
					$result_type = 'nt';
					break;
			}
			$this->lastReslutDetail[$row['testcase_type']][$row['testcase_module']][$row['code']] = $row;
			$this->lastReslutMid[$row['testcase_type']][$row['prj']][$row['testcase_module']] = 
				array('testcase_type_id'=>$row['testcase_type_id'], 'prj_id'=>$row['prj_id'], 'testcase_module_id'=>$row['testcase_module_id'], 'isstream'=>$row['isstream']);
			if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['total_tested']))
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['total_tested'] = 0;
			$data[$row['testcase_type']][$row['prj']][$row['testcase_module']]['total_tested'] ++;
			if(empty($data[$row['testcase_type']][$row['prj']][$row['testcase_module']][$result_type]))
				$data[$row['testcase_type']][$row['prj']][$row['testcase_module']][$result_type] = 0;
			$data[$row['testcase_type']][$row['prj']][$row['testcase_module']][$result_type] ++;

			if(empty($this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']]['total_tested']))
				$this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']]['total_tested'] = 0;
			$this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']]['total_tested'] ++;
			if(empty($this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']][$result_type]))
				$this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']][$result_type] = 0;
			$this->lastReslutByPrj[$row['prj']][$row['testcase_type']][$row['testcase_module']][$result_type] ++;
		}
		$ret = array();
		foreach($data as $testcase_type=>$testcase_type_data){
			foreach($testcase_type_data as $prj=>$prj_data){
				foreach($prj_data as $testcase_module=>$module_data){
					//获取有效Case数量
					$mid = $this->lastReslutMid[$testcase_type][$prj][$testcase_module];
					if($mid['isstream'] == 0){ // normal case
						$sql = "SELECT COUNT(*) AS cc ".
							" FROM prj_testcase_ver left join testcase on prj_testcase_ver.testcase_id=testcase.id".
							" left join testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id".
							" WHERE prj_id={$mid['prj_id']} and testcase_type_id={$mid['testcase_type_id']}".
							" and testcase_module_id={$mid['testcase_module_id']} ".
							" and testcase_ver.edit_status_id in (1,2) and testcase.isactive=1";
					}
					else{ // stream
						$sql = "SELECT COUNT(*) AS cc ".
							" FROM codec_stream where codec_stream_format_id={$mid['testcase_module_id']} ";
					}
					$res = $this->tool->query($sql);
					$row = $res->fetch();
					$total_cases = $row['cc'];
					$a = array('testcase_type'=>$testcase_type, 'testcase_module'=>$testcase_module, 'prj'=>$prj);
					$a = array_merge($a, $module_data);
					if(!empty($a['pass']))
						$a['pass_rate'] = $a['pass'] / $a['total_tested'];
					else
						$a['pass_rate'] = 0;
					$a['total_cases'] = $total_cases;
					$ret[] = $a;
				}
			}
		}
// print_r($ret);
		return $ret;
	}
		
	//按每个active的Project，统计Case的LastResult情况
	protected function getLastResultSheetByPrj(){
		$header = array(
			array(
				array('label'=>'Project', 'index'=>'prj', 'width'=>'150'),
				array('label'=>'Testcase Type', 'index'=>'testcase_type'),
				array('label'=>'Module', 'index'=>'testcase_module'),
				array('label'=>'Total Valid Cases', 'index'=>'total_cases'),
				array('label'=>'Total Tested', 'index'=>'total_tested'),
				array('label'=>'Pass', 'index'=>'pass'),
				array('label'=>'Fail', 'index'=>'fail'),
				array('label'=>'NS', 'index'=>'ns'),
				array('label'=>'Timeout', 'index'=>'timeout'),
				
				// array('label'=>'NT', 'index'=>'nt'),
				// array('label'=>'NA', 'index'=>'na'),
				// array('label'=>'Others', 'index'=>'others'),
				array('label'=>'Pass Rate', 'index'=>'pass_rate', 'style'=>'percent'),
			),
		);
		$data = $this->getLastResultDataByPrj();
		$sheet = array('title'=>'Last_Result_By_Prj', 'startRow'=>2, 'startCol'=>1, 
			'pre_text'=>"Last Result From {$this->params['last_result_from']} to {$this->params['last_result_to']}",
			'header'=>array(
				'rows'=>$header, 
			), 
			'data'=>$data, 
			'groups'=>array(
				array('index'=>'prj'),
				array('index'=>'testcase_type', 'subtotal'=>array('locate'=>'testcase_module', 'fields'=>array('total_cases', 'total_tested', 'pass', 'fail', 'ns', 'timeout'))), 
			)
		);
// print_r($sheet);
		return $sheet;
	}
	
	protected function getLastResultDataByPrj(){
		$ret = array();
		foreach($this->lastReslutByPrj as $prj=>$prj_data){
			foreach($prj_data as $testcase_type=>$testcase_type_data){
				foreach($testcase_type_data as $testcase_module=>$module_data){
					//获取有效Case数量
					//获取有效Case数量
					$mid = $this->lastReslutMid[$testcase_type][$prj][$testcase_module];
					if($mid['isstream'] == 0){ // normal case
						$sql = "SELECT COUNT(*) AS cc ".
							" FROM prj_testcase_ver left join testcase on prj_testcase_ver.testcase_id=testcase.id".
							" left join testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id".
							" WHERE prj_id={$mid['prj_id']} and testcase_type_id={$mid['testcase_type_id']}".
							" and testcase_module_id={$mid['testcase_module_id']} ".
							" and testcase_ver.edit_status_id in (1,2) and testcase.isactive=1";
					}
					else{ // stream
						$sql = "SELECT COUNT(*) AS cc ".
							" FROM codec_stream where codec_stream_format_id={$mid['testcase_module_id']} ";
					}
					$res = $this->tool->query($sql);
					$row = $res->fetch();
					$total_cases = $row['cc'];
					$a = array('testcase_type'=>$testcase_type, 'testcase_module'=>$testcase_module, 'prj'=>$prj);
					$a = array_merge($a, $module_data);
					if(!empty($a['pass']))
						$a['pass_rate'] = $a['pass'] / $a['total_tested'];
					else
						$a['pass_rate'] = 0;
					$a['total_cases'] = $total_cases;
					$ret[] = $a;
				}
			}
		}
// print_r($ret);
		return $ret;	
	}
	
	protected function getNormalCaseLastResultSql(){
		$validResult = implode(',', array(RESULT_TYPE_PASS, RESULT_TYPE_FAIL, RESULT_TYPE_NS, RESULT_TYPE_TIMEOUT));
		$strTestcaseType = '';
		$where = " prj.isactive=".ISACTIVE_ACTIVE;
		if(!empty($this->params['testcase_type_id'])){
			$where .= " AND testcase.testcase_type_id IN (".implode(',', $this->params['testcase_type_id']).")";
		}
		if(!empty($this->params['prj_ids'])){
			$where .= " AND prj.id IN (".implode(',', $this->params['prj_ids']).")";
		}
		if(!empty($this->params['last_result_from']))
			$where .= " AND tested>='{$this->params['last_result_from']}'";
		if(!empty($this->params['last_result_to']))
			$where .= " AND tested<='{$this->params['last_result_to']}'";
			
		$lastNormalSql = " SELECT testcase_id, prj_id, max(tested) as last_result ".
			" FROM testcase_last_result left join prj on prj.id=testcase_last_result.prj_id".
			" left join testcase on testcase_last_result.testcase_id=testcase.id".
			" WHERE $where AND testcase_last_result.result_type_id IN ($validResult) and testcase_last_result.codec_stream_id=0".
			" GROUP BY testcase_id, prj_id";
		
		$sql = " SELECT testcase.testcase_type_id, testcase_type.name as testcase_type, testcase.code, testcase.testcase_module_id, testcase_module.name as testcase_module, ".
			" prj.id as prj_id, prj.name as prj, A.result_type_id, cycle.name as cycle, A.tested, 0 as isstream ".
			" FROM testcase_last_result A ".
			" left join ($lastNormalSql) last_result on A.prj_id=last_result.prj_id AND A.testcase_id=last_result.testcase_id and A.tested=last_result.last_result ".
			" left join testcase on A.testcase_id=testcase.id".
			" left join testcase_type on testcase.testcase_type_id=testcase_type.id".
			" left join testcase_module on testcase.testcase_module_id=testcase_module.id".
			" left join prj on A.prj_id=prj.id".
			" left join cycle_detail on cycle_detail.id=A.cycle_detail_id".
			" left join cycle on cycle.id=cycle_detail.cycle_id".
			" WHERE $where AND A.result_type_id IN ($validResult) and A.codec_stream_id=0".
			" GROUP BY A.testcase_id, A.prj_id".
			" ORDER BY testcase_type, prj";
		return $sql;
	}
	
	protected function getStreamCaseLastResultSql(){
		$validResult = implode(',', array(RESULT_TYPE_PASS, RESULT_TYPE_FAIL, RESULT_TYPE_NS, RESULT_TYPE_TIMEOUT));
		$where = " prj.isactive=".ISACTIVE_ACTIVE;
		if(!empty($this->params['prj_ids'])){
			$where .= " AND prj.id IN (".implode(',', $this->params['prj_ids']).")";
		}
		if(!empty($this->params['last_result_from']))
			$where .= " AND tested>='{$this->params['last_result_from']}'";
		if(!empty($this->params['last_result_to']))
			$where .= " AND tested<='{$this->params['last_result_to']}'";
			
		$lastSql = " SELECT codec_stream_id, prj_id, max(tested) as last_result FROM testcase_last_result left join prj on prj.id=testcase_last_result.prj_id".
			" WHERE $where and testcase_last_result.codec_stream_id>0 AND testcase_last_result.result_type_id IN ($validResult)".
			" GROUP BY codec_stream_id, prj_id";
		
		$sql = " SELECT 2 as testcase_type_id, 'CODEC' as testcase_type, codec_stream.code, codec_stream.codec_stream_format_id as testcase_module_id, codec_stream_format.name as testcase_module, ".
			" prj.id as prj_id, prj.name as prj, A.result_type_id, cycle.name as cycle, A.tested, 1 as isstream ".
			" FROM testcase_last_result A ".
			" left join ($lastSql) last_result on A.prj_id=last_result.prj_id AND A.codec_stream_id=last_result.codec_stream_id and A.tested=last_result.last_result ".
			" left join codec_stream on A.codec_stream_id=codec_stream.id".
			" left join codec_stream_format on codec_stream.codec_stream_format_id=codec_stream_format.id".
			" left join prj on A.prj_id=prj.id".
			" left join cycle_detail on cycle_detail.id=A.cycle_detail_id".
			" left join cycle on cycle.id=cycle_detail.cycle_id".
			" WHERE $where and A.codec_stream_id>0 AND A.result_type_id IN ($validResult)".
			" GROUP BY A.codec_stream_id, A.prj_id".
			" order by testcase_type, prj";
		return $sql;
	}
	
	protected function getLastResultDetailSheet(){
		$header = array(
			array(
				array('label'=>'Testcase Type', 'index'=>'testcase_type'),
				array('label'=>'Module', 'index'=>'testcase_module'),
				array('label'=>'Testcase', 'index'=>'code', 'width'=>300),
				array('label'=>'Project', 'index'=>'prj', 'width'=>'150'),
				array('label'=>'Test Result', 'index'=>'result_type', 'width'=>100),
				array('label'=>'Last Run Time', 'index'=>'tested', 'width'=>120),
				array('label'=>'Cycle', 'index'=>'cycle', 'width'=>400),
			),
		);
		$data = $this->getLastResultDetailData();
		$sheet = array('title'=>'Last_Result_Detail', 'startRow'=>2, 'startCol'=>1, 
			// 'pre_text'=>"Not Run Cases from {$this->params['not_tested_from']} to {$this->params['not_tested_to']} (auto+manual)",
			'header'=>array(
				'rows'=>$header, 
			), 
			'data'=>$data, 
			// 'groups'=>array(
				// array('index'=>'testcase_type'),
				// array('index'=>'prj'),
				// array('index'=>'testcase_module'), 
			// )
		);
// print_r($sheet);
		return $sheet;
	}
		
	protected function getLastResultDetailData(){
		$data = array();
				// $this->detail[$row['testcase_type']][$row['prj']][$row['code']] = $row;
		foreach($this->lastReslutDetail as $testcase_type=>$testcase_type_data){
			foreach($testcase_type_data as $testcase_module=>$testcase_module_data){
				foreach($testcase_module_data as $code=>$code_data){
					$testcase_module = $code_data['testcase_module'];
					$prj = $code_data['prj'];
					$cycle = $code_data['cycle'];
					$tested = $code_data['tested'];
					$result_type = 'Pass';
					switch($code_data['result_type_id']){
						case RESULT_TYPE_PASS:
							$result_type = 'pass';
							break;
						case RESULT_TYPE_FAIL:
							$result_type = 'fail';
							break;
						case RESULT_TYPE_NS:
							$result_type = 'ns';
							break;
						case RESULT_TYPE_TIMEOUT:
							$result_type = 'timeout';
							break;
						case RESULT_TYPE_NA:
							$result_type = 'na';
							break;
						case RESULT_TYPE_NT:
							$result_type = 'nt';
							break;
					}					
					$data[] = compact('testcase_type', 'prj', 'testcase_module', 'code', 'cycle', 'result_type', 'tested');
				}
			}
		}
		return $data;
	}
	
	//按每个active的Project，统计Case的Edit情况
	protected function getModifiedTestcaseSheet(){
		$sheet = array('title'=>'Modified Testcase', 'startRow'=>2, 'startCol'=>1);
		$ver = tableDescFactory::get('xt', 'testcase_ver');
		$sheet['header']['rows'] = array($this->getTitle($ver));
		foreach($sheet['header']['rows'] as $row=>&$rowData){
			foreach($rowData as $col=>&$cell){
				if($cell['name'] == 'id' || $cell['name'] == 'old_id')
					unset($rowData[$col]);
				else
					$cell['hidden'] = false;
			}
		}
		array_unshift($sheet['header']['rows'][0], array('label'=>'Testcase Type', 'index'=>'testcase_type'), array('label'=>'Testcase Module', 'index'=>'testcase_module'));
		$sheet['data'] = $this->getModifiedTestcaseData();
		$conditions = array();
		
		$sheet['pre_text'] = " Conditions:";
		if(!empty($this->params['modified_from']))
			$conditions[] = " From {$this->params['modified_from']}";
		if(!empty($this->params['modified_to']))
			$conditions[] = " To {$this->params['modified_to']}";
		if (!empty($this->params['prj_ids'])){
			$conditions[] = " Prj: ".implode(',', $this->params['prj_ids']);
		}
		if (!empty($this->params['testcase_type_id'])){
			$conditions[] = " Testcase Type: ".implode(',', $this->params['testcase_type_id']);
		}
		$sheet['pre_text'] = "Conditions:".implode(", ", $conditions);
		return $sheet;
	}
	
	protected function getModifiedTestcaseData(){
		$ver = tableDescFactory::get($this->params['db'], 'testcase_ver');
		$searchConditions = array();
		if(!empty($this->params['modified_from']))
			$searchConditions[] = array('field'=>'updated', 'op'=>'>=', 'value'=>$this->params['modified_from']);
		if(!empty($this->params['modified_to']))
			$searchConditions[] = array('field'=>'updated', 'op'=>'<=', 'value'=>$this->params['modified_to']);
		if (!empty($this->params['prj_ids'])){
			$searchConditions[] = array('field'=>'prj_testcase_ver.prj_id', 'op'=>'IN', 'value'=>$this->params['prj_ids']);
		}
		if (!empty($this->params['testcase_type_id'])){
			$searchConditions[] = array('field'=>'testcase.testcase_type_id', 'op'=>'IN', 'value'=>$this->params['testcase_type_id']);
		}
// print_r($searchConditions);		
		$sqls = $ver->calcSqlComponents(array('db'=>$this->params['db'], 'table'=>'testcase_ver', 'searchConditions'=>$searchConditions), false);
		$sqls['main']['from'] .= " LEFT JOIN testcase on testcase.id=testcase_ver.testcase_id left join testcase_module on testcase.testcase_module_id=testcase_module.id left join testcase_type on testcase.testcase_type_id=testcase_type.id";
		$sqls['main']['fields'] .= ", testcase_module.name as testcase_module, testcase_type.name as testcase_type";
		$sqls['order'] = "testcase_module ASC, testcase_id ASC";
// print_r($sqls);
		$sql = $ver->getSql($sqls, false);
// print_r($sql);
		$res = $this->tool->query($sql);
		$rows = array();
		while($row = $res->fetch()){
			$row = $ver->getMoreInfoForRow($row);
			$rows[] = $row; 
		}
		return $rows;
	}
		
	protected function getSubtotalRow($sheetIndex, $field, $subtotal, $last){
		$subtotalRow = parent::getSubtotalRow($sheetIndex, $field, $subtotal, $last);
		$sheet_title = $this->params['sheets'][$sheetIndex]['title'];
		switch($sheet_title){
			case 'Execute_Rate':
				$subtotalRow['p1_3_exe_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'p1_3_run', 'p1_3_total_cases');
				// $p1_total_cases = $this->getCalculatedValue($sheetIndex, 'p1_total_cases', $this->params['sheets'][$sheetIndex]['nextRow']);
// print_r("p1_total_cases = $p1_total_cases \n");
				// if(!empty($p1_total_cases))
				$subtotalRow['p1_exe_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'p1_run', 'p1_total_cases');
				break;
			case 'Last_Result':
			case 'Last_Result_By_Prj':
				$subtotalRow['pass_rate'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'pass', 'total_tested');
				break;
				
		}
		return $subtotalRow;
	}
};

?>
