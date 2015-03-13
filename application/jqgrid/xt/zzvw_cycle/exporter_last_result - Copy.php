<?php
require_once('exporter_excel.php');
defined('WITH_RESULT_STATIC_COLUMNS') || define('WITH_RESULT_STATIC_COLUMNS', 1);
defined('WITH_RESULT_DETAIL_COLUMNS') || define('WITH_RESULT_DETAIL_COLUMNS', 2);
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
	protected $prjCount = 0;
	protected function init($params = array()){
		parent::init($params);
//print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_cycle [export_type] => last_result [id] => array(10,9) [real_table] => cycle ) 
		$this->db = dbFactory::get($this->params['db']);
		
		$this->params['id'] = implode(',', $this->params['id']);
		$this->testResultData();
		$this->params['id'] = explode(',', $this->params['id']);
	}
	
	public function setOptions($jqgrid_action){
		$this->params['sheets'] = array(
			// 0=>$this->getCoverSheet($jqgrid_action),
			0=>$this->getPrjCompilerModuleSheet($jqgrid_action)
		);
		if (!empty($this->params['include_coverage'][0])){
			if (empty($this->params['coverage_end']))
				$this->params['coverage_end'] = date('Y-m-d');
			$this->params['sheets'][1] = $this->getCoverageSheet($jqgrid_action);
			$this->moduleSheet = 2;
		}
		$this->params['sheets'][] = $this->getModulePrjCompilerSheet($jqgrid_action);
		$this->params['sheets'][] = $this->getDetailSheet($jqgrid_action);
		// return;
		// cycle 
		// foreach($this->params['id'] as $cycle_id){
			// $this->params['sheets'][] = $this->getCycleSheet($jqgrid_action, $cycle_id);
		// }
		// project
		foreach($this->baseData['prj'] as $prj_id=>$prj){
			$this->params['sheets'][] = $this->getPrjSheet($jqgrid_action, $prj_id, $prj['name']);
		}
// print_r($this->params['sheets']);
	}
	
	// 由于生成的Sheet很多，需要一个Cover来管理跳转
	protected function getCoverSheet($jqgrid_action){
		$sheet = array('title'=>'Cover', 'startRow'=>3, 'startCol'=>1);
		$row0 = array('no'=>array('index'=>'no', 'label'=>'No'), 'sheet_name'=>array('index'=>'sheet_name', 'label'=>'Sheet Name'), 'description', 'total_case'=>array('label'=>'Number of Testcases'), 'pass', 'fail', 'pass_rate');
		$sheet['header']['rows'][0] = $row0;
		$sheet['data'] = $this->getData('cover');
		return $sheet;
	}
	
	protected function getPrjCompilerModuleSheet($jqgrid_action){
		$sheet = array('title'=>'Project-Module', 'startRow'=>2, 'startCol'=>1);
		
		$row = array('prj', 'compiler'=>array('hidden'=>true), 'module');
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_STATIC_COLUMNS);

		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'module'=>array(2, 3));
		$sheet['data'] = $this->getData('prj_compiler_module');
		$sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), 
			array('index'=>'compiler', 'subtotal'=>array('locate'=>'module', 'fields'=>$subtotalFields)));
		return $sheet;
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
		$row = array('module', 'prj', 'compiler');
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_STATIC_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'module'=>array(2, 3));
		$sheet['data'] = $this->getData('module_prj_compiler');
		$sheet['groups'] = array(array('index'=>'module', 'subtotal'=>array('locate'=>'prj', 'fields'=>$subtotalFields)), array('index'=>'prj'), array('index'=>'compiler'));
		return $sheet;
	}
	
	protected function getDetailSheet($jqgrid_action){
		$sheet = array('title'=>'Detail', 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'codec_stream', 'testcase', 'expected_result'=>array('hidden'=>true), 'prj', 'compiler'=>array('hidden'=>true), 
			'build_target'=>array('hidden'=>true), 'test_env'=>array('hidden'=>true));
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_DETAIL_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array(
			'testcase'=>array(2, 3), 'codec_stream'=>array(2,3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 'prj'=>array(2, 3), 'compiler'=>array(2, 3), 
			'build_target'=>array(2, 3), 'test_env'=>array(2, 3)
		);
		$sheet['data'] = $this->getData('detail');
		$sheet['groups'] = array(array('index'=>'module'),  array('index'=>'codec_stream'), array('index'=>'testcase'), array('index'=>'expected_result'), 
			array('index'=>'prj'), array('index'=>'compiler'), array('index'=>'build_target'), array('index'=>'test_env'));
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
		$sheet['data'] = $this->getData('prj_'.$prj_id);
		$sheet['groups'] = array(array('index'=>'module'), array('index'=>'codec_stream'), array('index'=>'testcase'), array('index'=>'expected_result'),
			array('index'=>'compiler'), array('index'=>'build_target'), array('index'=>'test_env'));
//		$sheet['groups'] = array('code'=>array(), 'module'=>array());
		return $sheet;
	}
	
	protected function row2header($rows, $flag = WITH_RESULT_STATIC_COLUMNS){
		$header = array();
		$subtotal = array();
		foreach($rows as $i=>$row){
			foreach($row as $field=>$prop){
				$header[$i][] = $this->getFieldHeader($field, $prop);
			}
			switch($flag){
				case WITH_RESULT_STATIC_COLUMNS:
					foreach(array('total', 'pass', 'fail', 'others', 'pass_rate') as $field=>$prop){
						if($i == 0){
							$h = $this->getFieldHeader($field, $prop);
							$h['cols'] = $this->cycleCount;
							if($this->prjCount == 1)
								$h['cols'] ++;
							$header[$i][] = $h;
						}
						else{
							foreach($this->params['id'] as $cycle_id){
								$h = $this->getFieldHeader($field, $prop);
								$h['index'] = 'cycle_'.$cycle_id.'_'.$prop;
								$h['label'] = $this->baseData['cycle'][$cycle_id]['name'];
								$subtotal[] = $h['index'];
								$header[$i][] = $h;
							}
							if($this->prjCount == 1){
								$header[$i][] = array('label'=>'Final', 'index'=>'final_'.$prop);
							}
						}
					}
					break;
				case WITH_RESULT_DETAIL_COLUMNS:
					$detail_results = array('build_result', 'result_type', 'cr');
					$columns = count($detail_results);
					foreach($this->params['id'] as $cycle_id){
						if ($i ==  0){
							$h = array('label'=>$this->baseData['cycle'][$cycle_id]['name'], 'cols'=>$columns);
							$header[$i][] = $h;
						}
						else{
							foreach($detail_results as $field=>$r){
								$h = $this->getFieldHeader($field, $r);
								$h['index'] = 'cycle_'.$cycle_id.'_'.$r;
								$subtotal[] = $h['index'];
								$header[$i][] = $h;
							}
						}
					}
					break;
				case WITH_COVERAGE_COLUMNS:
					break;
			}
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
				$prop['label'] = 'Pass Rate';
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
	
	protected function getData($sheet, $searchConditions = array(), $order = array()){
		return empty($this->data[$sheet]) ? array() : $this->data[$sheet];
	}
	
	function getBaseData($base_tables, $base_ids){
// print_r($base_ids);
		foreach($base_tables as $table=>$fields){
			if ($table == 'build_result' || empty($base_ids[$table])) continue;
			$strIds = implode(',', $base_ids[$table]);
			$res = $this->db->query("SELECT id, $fields FROM $table WHERE id IN ($strIds)");
			while($row = $res->fetch()){
				$this->baseData[$table][$row['id']] = $row;
			}
		}
// print_r($this->baseData);
	}
	
	function testResultData(){
		$data = array();
		
		$base_table = array('codec_stream_format'=>'name', 'testcase_module'=>'name', 'test_env'=>'name', 
			'testcase_ver'=>'expected_result', 'prj'=>'name', 'compiler'=>'name', 'build_target'=>'name', 
			'build_result'=>'name', 'result_type'=>'name', 'cycle'=>'name');
		$base_ids = array();
		
		$strIds = $this->params['id'];
		$lastStreamId = 0;
		$streamResult = 0;
		$lastRow = array();
		$sql = "SELECT cycle.id as cycle_id, cycle.prj_id, cycle.compiler_id, cycle.rel_id, cycle.build_target_id, ".
			" cycle_detail.testcase_id, cycle_detail.testcase_ver_id, cycle_detail.codec_stream_id, cycle_detail.test_env_id, ".
			" cycle_detail.build_result_id, cycle_detail.result_type_id, cycle_detail.defect_ids, cycle_detail.codec_stream_id,".
			" testcase.code as testcase, testcase.testcase_module_id,".
			" codec_stream.code as codec_stream, codec_stream.codec_stream_format_id ".
			" FROM cycle LEFT JOIN cycle_detail ON cycle.id=cycle_detail.cycle_id".
			" LEFT JOIN testcase on cycle_detail.testcase_id=testcase.id".
			" LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id".
			" WHERE cycle.id IN ($strIds)".
			" ORDER BY codec_stream_format_id ASC, testcase_module_id ASC, codec_stream ASC, testcase ASC, cycle.created ASC";
// print_r($sql);
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			foreach($base_table as $b=>$field){
				if (!empty($row[$b.'_id'])){
					if ($b == 'build_result')
						$base_ids['result_type'][$row[$b.'_id']] = $row[$b.'_id'];
					else
						$base_ids[$b][$row[$b.'_id']] = $row[$b.'_id'];
				}
			}
			
			$case = 'testcase';
			if ($row['codec_stream_id'] == 0){
				$type = 'normal';
				$module_id = $row['testcase_module_id'];
				$data['detail'][$type][$module_id][''][$row[$case]][$row['testcase_ver_id']][$row['prj_id']][$row['compiler_id']][$row['build_target_id']][$row['test_env_id']][$row['cycle_id']] = $row;
				$this->incTotal($data, $type, $row, $module_id, $row['result_type_id']);
			}
			else{
				$module_id = $row['codec_stream_format_id'];
				// $case = 'codec_stream';
				$type = 'codec_stream';
				$data['detail'][$type][$module_id][$row['codec_stream']][$row[$case]][$row['testcase_ver_id']][$row['prj_id']][$row['compiler_id']][$row['build_target_id']][$row['test_env_id']][$row['cycle_id']] = $row;
				//合并，并检测结果。对同一个stream认为是一个Case，除非对应的所有trickmode的结果为pass，否则就认为结果为fail
				if(empty($lastStreamId)){
					$lastStreamId = $row['codec_stream_id'];
					$streamResult = $row['result_type_id'];
				}
				elseif($row['codec_stream_id'] != $lastStreamId){
					$this->incTotal($data, $type, $lastRow, $lastRow['codec_stream_format_id'], $streamResult);
					$streamResult = $row['result_type_id'];
					$lastStreamId = $row['codec_stream_id'];
				}
				else{
					if($row['result_type_id'] != RESULT_TYPE_PASS){
						// $streamResult = RESULT_TYPE_FAIL;
						if($streamResult == RESULT_TYPE_PASS)
							$streamResult = $row['result_type_id'];
						elseif($streamResult != RESULT_TYPE_FAIL)
							$streamResult = $row['result_type_id'];
					}
				}
			}
			$lastRow = $row;
		}
		if($lastStreamId){ // 处理最后一条
// print_r($lastRow);
// print_r("streamResult = $streamResult, module_id=$module_id<<<\n");
			$this->incTotal($data, $type, $lastRow, $lastRow['codec_stream_format_id'], $streamResult);
		}
		$this->getBaseData($base_table, $base_ids);
		$this->cycleCount = count($this->baseData['cycle']);
//		$this->prjCount = count($this->baseData['prj']);
		
		$this->handlePrjCompilerModuleData($data['prj_compiler_module']);
		$this->handleModulePrjCompilerData($data['module_prj_compiler']);
		$this->handleDetailData($data['detail']);
	}
	
	protected function incTotal(&$data, $type, $row, $module_id, $result){
		if (empty($data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']]['total']))
			$data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']]['total'] = 1;
		else
			$data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']]['total'] ++;
		
		if (empty($data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']]['total']))
			$data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']]['total'] = 1;
		else
			$data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']]['total'] ++;
			
		switch($result){
			case RESULT_TYPE_PASS:
				$name = 'pass';
				break;
			case RESULT_TYPE_FAIL:
				$name = 'fail';
				break;
			default:
				$name = 'others';
				break;
		}

		if (empty($data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']][$name]))
			$data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']][$name] = 1;
		else
			$data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$module_id][$row['cycle_id']][$name] ++;
			
		if (empty($data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']][$name]))
			$data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']][$name] = 1;
		else
			$data['module_prj_compiler'][$type][$module_id][$row['prj_id']][$row['compiler_id']][$row['cycle_id']][$name] ++;
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
	
	protected function handlePrjCompilerModuleData($data){
		// $data['prj_compiler_module'][$type][$row['prj_id']][$row['compiler_id']][$row['testcase_module_id']][$cycle_id][$name] = 1;
		$this->data['prj_compiler_module'] = array();
// print_r($data);		
		foreach(array('codec_stream', 'normal') as $type){
			if ($type == 'codec_stream')
				$module = 'codec_stream_format';
			else
				$module = 'testcase_module';
			if (empty($data[$type])) continue;
			foreach($data[$type] as $prj_id=>$prj_data){
				foreach($prj_data as $compiler_id=>$compiler_data){
					foreach($compiler_data as $module_id=>$module_data){
// print_r("module_id = $module_id<<<<\n");					
						//$this->data['prj_compiler_module'][$i] 
						$row = array('prj'=>$this->baseData['prj'][$prj_id]['name'], 'compiler'=>$this->baseData['compiler'][$compiler_id]['name'],
							'module'=>empty($this->baseData[$module][$module_id]['name']) ? '' : $this->baseData[$module][$module_id]['name']);
						foreach($module_data as $cycle_id=>$cycle_data){
							foreach($cycle_data as $result_type=>$result_count){
								$row['cycle_'.$cycle_id.'_'.$result_type] = $result_count;
							}
							$row['cycle_'.$cycle_id.'_pass_rate'] = !empty($row['cycle_'.$cycle_id.'_pass']) ? $row['cycle_'.$cycle_id.'_pass'] / $row['cycle_'.$cycle_id.'_total'] : 0;
						}
						$this->data['prj_compiler_module'][] = $row;
					}
				}
			}
		}
// print_r($this->data['prj_compiler_module']);
	}
	
	protected function handleModulePrjCompilerData($data){
		// $data['module_prj_compiler'][$type][$row['testcase_module_id']][$row['prj_id']][$row['compiler_id']][$cycle_id][result] = 1;
		$this->data['module_prj_compiler'] = array();
		foreach(array('codec_stream', 'normal') as $type){
			if ($type == 'codec_stream')
				$module = 'codec_stream_format';
			else
				$module = 'testcase_module';
			if (empty($data[$type]))continue;
			foreach($data[$type] as $module_id=>$module_data){
				foreach($module_data as $prj_id=>$prj_data){
					foreach($prj_data as $compiler_id=>$compiler_data){
						$row = array('prj'=>$this->baseData['prj'][$prj_id]['name'], 'compiler'=>$this->baseData['compiler'][$compiler_id]['name'], 
							'module'=>empty($this->baseData[$module][$module_id]['name']) ? '' : $this->baseData[$module][$module_id]['name']);
						foreach($compiler_data as $cycle_id=>$cycle_data){
							foreach($cycle_data as $result_type=>$result_count){
								$row['cycle_'.$cycle_id.'_'.$result_type] = $result_count;
							}
							$row['cycle_'.$cycle_id.'_pass_rate'] = !empty($row['cycle_'.$cycle_id.'_pass']) ? $row['cycle_'.$cycle_id.'_pass'] / $row['cycle_'.$cycle_id.'_total'] : 0;
						}
						$this->data['module_prj_compiler'][] = $row;
					}
				}
			}
		}
	}
	
	protected function handleDetailData($data){
	// $data['detail'][$type][$row['testcase_module_id']][$row['codec_stream_id']][$row['testcase_id']][$row['testcase_ver_id']][$row['prj_id']][$row['compiler_id']][$row['build_target_id']][$row['test_env_id']][$row['cycle_id']] = $row;
		$this->data['detail'] = array();
		foreach(array('codec_stream', 'normal') as $type){
			if ($type == 'codec_stream')
				$module = 'codec_stream_format';
			else
				$module = 'testcase_module';
			if (empty($data[$type]))continue;
			foreach($data[$type] as $module_id=>$module_data){
				foreach($module_data as $codec_stream=>$codec_stream_data){
					foreach($codec_stream_data as $testcase=>$testcase_data){
						foreach($testcase_data as $ver_id=>$ver_data){
							foreach($ver_data as $prj_id=>$prj_data){
								foreach($prj_data as $compiler_id=>$compiler_data){
									foreach($compiler_data as $build_target_id=>$build_target_data){
										foreach($build_target_data as $test_env_id=>$test_env_data){
											$row = array('codec_stream'=>$codec_stream, 'testcase'=>$testcase);
											$row['prj'] = empty($prj_id) ? '' : $this->baseData['prj'][$prj_id]['name'];
											$row['module'] = empty($this->baseData[$module][$module_id]['name']) ? '' : $this->baseData[$module][$module_id]['name'];
											$row['expected_result'] = empty($ver_id) ? '' : $this->baseData['testcase_ver'][$ver_id]['expected_result'];
											$row['compiler'] = empty($compiler_id) ? '' : $this->baseData['compiler'][$compiler_id]['name'];
											$row['test_env'] = empty($test_env_id) ? '' : $this->baseData['test_env'][$test_env_id]['name'];
											foreach($test_env_data as $cycle_id=>$cycle_data){
												$row['cycle_'.$cycle_id.'_build_result'] = empty($cycle_data['build_result_id']) ? '' : $this->baseData['result_type'][$cycle_data['build_result_id']]['name'];
												$row['cycle_'.$cycle_id.'_result_type'] = empty($cycle_data['result_type_id']) ? '' : $this->baseData['result_type'][$cycle_data['result_type_id']]['name'];
												$row['cycle_'.$cycle_id.'_cr'] = $cycle_data['defect_ids'];

												if (!isset($this->data['cycle_'.$cycle_id]))
													$this->data['cycle_'.$cycle_id] = array();
												$this->data['cycle_'.$cycle_id][] = $row;
											}
											$this->data['detail'][] = $row;
											if (!isset($this->data['prj_'.$prj_id]))
												$this->data['prj_'.$prj_id] = array();
											$this->data['prj_'.$prj_id][] = $row;
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	protected function getSubtotalRow($sheetIndex, $field, $subtotal, $last){
		$subtotalRow = parent::getSubtotalRow($sheetIndex, $field, $subtotal, $last);
		if ($sheetIndex == 0 || $sheetIndex == $this->moduleSheet){
			foreach($subtotal['fields'] as $subfield){
				if (preg_match('/^(.*?)_pass_rate$/', $subfield, $matches)){
					$pass = $matches[1].'_pass';
					$total = $matches[1].'_total';
					$subtotalRow[$subfield] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], $pass, $total);
				}
			}
		}
		else if ($this->params['include_coverage'][0] && $sheetIndex == 1){
			$subtotalRow['coverage_1_3'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_1_3', 'total_1_3');
			$subtotalRow['coverage_1'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_1', 'total_1');
			$subtotalRow['coverage_2'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_2', 'total_2');
			$subtotalRow['coverage_3'] = $this->div($sheetIndex, $this->params['sheets'][$sheetIndex]['nextRow'], 'runed_3', 'total_3');
		}
		return $subtotalRow;
	}
	
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$v = $content[$headerIndex];
		$style = parent::calcStyle($sheetIndex, $headerIndex, $v, $default);
		if ($sheetIndex > $this->moduleSheet && !empty($v) && (stripos($headerIndex, 'result_type_id') !== false || stripos($headerIndex, 'build_result_id') !== false)){
			if (strtolower($v) != 'pass'){//RESULT_TYPE_PASS){
				$style = 'warning';
			}
		}
		return $style;
	}

};
?>
 