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
	protected $prjMerge = false;
	protected $cycleInfo = array();
	protected $strIds = '';
	protected function init($params = array()){
		parent::init($params);
//print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_cycle [export_type] => last_result [id] => array(10,9) [real_table] => cycle ) 
		$this->tool = dbFactory::get($this->params['db']);
		$this->tool->query("SET SESSION group_concat_max_len = 1000000");
		if(!is_array($this->params['id'])){
			$id = array();
			$id[] = $this->params['id'] ;
			$this->params['id'] = $id;
		}
		$this->strIds = implode(',', $this->params['id']);
		$this->cycleCount = count($this->params['id']);
		$res = $this->tool->query("SELECT * FROM cycle WHERE id IN ({$this->strIds})");
		while($cycle = $res->fetch())
			$this->cycleInfo[$cycle['id']] = $cycle;
		if($this->cycleCount > 1){
			$res = $this->tool->query("SELECT COUNT(distinct prj_id) AS cc FROM cycle WHERE id IN ({$this->strIds})");
			$row = $res->fetch();
			if($row['cc'] == 1)
			$this->prjMerge = true;
		}
		$this->testResultData();
//		$this->params['id'] = explode(',', $this->params['id']);
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
		// return;
		$this->params['sheets'][] = $this->getDetailSheet($jqgrid_action);
		return;
		// if($this->cycleCount > 0){
			// foreach($this->cycleInfo as $cycle_id=>$cycle){
				// $this->params['sheets'][] = $this->getCycleSheet($jqgrid_action, $cycle_id);
			// }
		// }
		// return;
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
		
		$sheet['data'] = $this->getPrjModuleData();
		$sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), 
			array('index'=>'compiler', 'subtotal'=>array('locate'=>'module', 'fields'=>$subtotalFields)));
		return $sheet;
	}
	
	protected function getSql($order, $isStream = true, $lastResult = false){
		$module = 'testcase_module';
		$testcase = 'testcase';
		if($isStream){
			$module = 'codec_stream_format';
			$testcase = 'codec_stream';
		}

		$detail = 'cycle_detail';
		if($lastResult){
			$detail = "(".
				" SELECT cycle_id, codec_stream_id, result_type_id, testcase_id ".
				" FROM cycle_detail Z ".
				" WHERE cycle_id in ({$this->strIds}) ".
				" AND (codec_stream_id, testcase_id, cycle_id) in (".
					" SELECT codec_stream_id, testcase_id, max(cycle_id) ".
					" FROM cycle_detail".
					" WHERE cycle_id in ({$this->strIds}) AND codec_stream_id=Z.codec_stream_id AND testcase_id=Z.testcase_id ".
					" GROUP BY codec_stream_id, testcase_id".
				" )".
				" GROUP BY codec_stream_id, testcase_id".
			" )";
		}
		
		$sql = "SELECT prj.name as prj, module.name as module, cycle.prj_id, cycle.compiler_id,".
			" detail.cycle_id as cycle_id, detail.{$testcase}_id, ";
		if($isStream)
			$sql .= " group_concat(concat(detail.codec_stream_id, ':', detail.result_type_id)) as result_type_id,";
		else	
			$sql .= "detail.result_type_id, ";
		$sql .= " count(distinct {$testcase}_id) as cases".
			" FROM cycle LEFT JOIN $detail detail ON cycle.id=detail.cycle_id".
			" LEFT JOIN prj ON cycle.prj_id=prj.id".
			" LEFT JOIN $testcase tc ON detail.{$testcase}_id=tc.id".
			" LEFT JOIN $module module on module.id=tc.{$module}_id".
			" WHERE detail.cycle_id IN ({$this->strIds})";
		if($isStream)
			$sql .= " AND detail.codec_stream_id>0";
		else
			$sql .= " AND detail.codec_stream_id=0";
		
		$sql .= " GROUP BY ";
		if(!$lastResult)
			$sql .= "cycle_id, ";
		$sql .= "prj_id, compiler_id, {$module}_id";
		if(!$isStream)
			$sql .= ", result_type_id";
		if($order == 'prj')
			$sql .= " ORDER BY prj ASC, module ASC, cycle_id, start_date ASC";
		else	
			$sql .= " ORDER BY module ASC, prj ASC, cycle_id, start_date ASC";
// print_r($sql);
		return $sql;
	}
	
	protected function getStreamSql($order = 'prj', $lastResult = false){
		return $this->getSql($order, true, $lastResult);
	}

	protected function handleStreamData($row, &$data, $lastResult = false){
		$m = 'module';
		$f = 'cases';
		if(empty($data[$row['prj_id']][$row['compiler_id']][$row[$m]]))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]] = $row;
			
		$field = 'cycle_'.$row['cycle_id'];
		if($lastResult)
			$field = 'final';
		if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total']))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'] = 0;
		$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'] += $row[$f];
		
		$streamResults = array();
		$results = explode(',', $row['result_type_id']);
// print_r($results);
		foreach($results as $er){
			list($s, $r) = explode(':', $er);
			$streamResults[$s][] = $r;
		}
// print_r($row[$m]);
// print_r($streamResults);

		foreach($streamResults as $stream_id=>$results){
			$name = 'pass';
			if(in_array(RESULT_TYPE_FAIL, $results)){ // fail
				$name = 'fail';
			}
			else{
				$results = array_unique($results);
				if(count($results) > 1 || $results[0] != RESULT_TYPE_PASS){// others
					$name = 'others';
				}
			}
			$field2 = $field.'_'.$name;
			if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2]))
				$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2] = 0;
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2] ++;
		}
		if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass']))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass'] = 0;
		$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass_rate'] = 
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass'] / $data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'];
// print_r($data[$row['prj_id']][$row['compiler_id']][$row[$m]]);
	}
	
	protected function getStreamData(&$data, $order = 'prj'){
		$sql = $this->getStreamSql($order, false);
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
// print_r($row);
			$this->handleStreamData($row, $data);
		}
		$sql = $this->getStreamSql($order, true);
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
// print_r($row);
			$this->handleStreamData($row, $data, true);
		}
	}
	
	protected function getCaseSql($order = 'prj', $lastResult = false){
		return $this->getSql($order, false, $lastResult);
	}
	
	protected function handleCaseData($row, &$data, $lastResult = false){
// print_r($row);
		$m = 'module';
		$f = 'cases';
		$field = 'cycle_'.$row['cycle_id'];
		if($lastResult)
			$field = 'final';
		if(empty($data[$row['prj_id']][$row['compiler_id']][$row[$m]]))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]] = $row;
		if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total']))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'] = 0;
		$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'] += $row[$f];
		$name = 'others';
		switch($row['result_type_id']){
			case RESULT_TYPE_PASS:
				$name = 'pass';
				break;
			case RESULT_TYPE_FAIL:
				$name = 'fail';
				break;
		}
		$field2 = $field.'_'.$name;
		if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2]))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2] = 0;
		$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field2] += $row[$f];
		if(!isset($data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass']))
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass'] = 0;
		$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass_rate'] = 
			$data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_pass'] / $data[$row['prj_id']][$row['compiler_id']][$row[$m]][$field.'_total'];
	}
	
	protected function getNormalCaseData(&$data, $order = 'prj'){
		$sql = $this->getCaseSql($order, false);
		$res = $this->tool->query($sql);
// print_r($sql)		;
		while($row = $res->fetch()){
			$this->handleCaseData($row, $data, false);
		}
		
		$sql = $this->getCaseSql($order, true);
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$this->handleCaseData($row, $data, true);
		}
	}
	
	protected function getPrjModuleData($order = 'prj'){
		$data = array();
		$this->getStreamData($data, $order);
// print_r($data);
		$this->getNormalCaseData($data, $order);
// print_r($data);
		$ret = array();
		foreach($data as $prj_id=>$prj_data){
			foreach($prj_data as $compiler_id=>$compiler_data){
				foreach($compiler_data as $module=>$module_data){
					$module_data['module'] = $module;
					$ret[] = $module_data;
				}
			}
		}
		return $ret;
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
		$sheet['data'] = $this->getPrjModuleData('module');
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
		// if($this->prjMerge == 1)
			// $sheet['header']['mergeCols']['final_result'] = array(2,3);
		$sheet['data'] = $this->getData('detail');
		$sheet['groups'] = array(array('index'=>'module'),  array('index'=>'codec_stream'), array('index'=>'testcase'), array('index'=>'expected_result'), 
			array('index'=>'prj'), array('index'=>'compiler'), array('index'=>'build_target'), array('index'=>'test_env'));
		return $sheet;
	}
/*	
	protected function getDetailData(){
		$data = array();

		$sql = "SELECT cycle.prj_id, cycle.compiler_id, cycle.build_target_id, ".
		" testcase.code as testcase, testcase.testcase_module_id,".
		" codec_stream.code as codec_stream, codec_stream.codec_stream_format_id, ".
		" cycle_detail.test_env_id, cycle_detail.codec_stream_id, cycle_detail.testcase_id, cycle_detail.build_result_id, cycle_detail.result_type_id,".
		" cycle_detail.defect_ids, cycle_detail.finish_time".
		" from cycle_detail LEFT JOIN cycle on cycle_detail.cycle_id=cycle.id".
		" LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id".
		" LEFT JOIN testcase on cycle_detail.testcase_id=testcase.id".
		" WHERE cycle_detail.cycle_id IN ({$this->strIds})";
			
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$m = 'codec_stream_format_id'
			if(empty($row['codec_stream_id']))
				$m = 'testcase_module_id';
				
			if(empty($data[$row[$m]][$row['codec_stream_id']][$row['testcase_id']]))
			$data[]
		}
	}
*/	
	protected function getCycleSheet($jqgrid_action, $cycle_id){
		$sheet = array('title'=>$this->cycleInfo[$cycle_id]['name'], 'startRow'=>2, 'startCol'=>1);
		$row = array('module', 'codec_stream', 'testcase', 'expected_result'=>array('hidden'=>true), 'test_env'=>array('hidden'=>true));
		list($header, $subtotalFields) = $this->row2header(array($row, $row), WITH_RESULT_DETAIL_COLUMNS);
		$sheet['header']['rows'] = $header;
		$sheet['header']['mergeCols'] = array('testcase'=>array(2, 3), 'codec_stream'=>array(2,3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 'test_env'=>array(2, 3));
		$sheet['data'] = $this->getData('cycle_'.$cycle_id);
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
		$sheet['data'] = $this->getData('prj_'.$prj_id);
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
			switch($flag){
				case WITH_RESULT_STATIC_COLUMNS:
					foreach(array('total', 'pass', 'fail', 'others', 'pass_rate') as $field=>$prop){
						if($i == 0){
							$h = $this->getFieldHeader($field, $prop);
							$h['cols'] = $this->cycleCount;
							if($this->prjMerge == 1)
								$h['cols'] ++;
							$header[$i][] = $h;
						}
						else{
							foreach($this->params['id'] as $cycle_id){
								$h = $this->getFieldHeader($field, $prop);
								$h['index'] = 'cycle_'.$cycle_id.'_'.$prop;
								$h['label'] = $this->cycleInfo[$cycle_id]['name'];
								// $h['hidden'] = $this->prjMerge;
								$subtotal[] = $h['index'];
								$header[$i][] = $h;
								$col ++;
							}
							if($this->prjMerge == 1){
								$h = array('label'=>'Final', 'index'=>'final_'.$prop);
								if($prop == 'pass_rate')
									$h['style'] = 'percent';
								$header[$i][] = $h;
									
								$subtotal[] = 'final_'.$prop;
							}
						}
					}
					break;
				case WITH_RESULT_DETAIL_COLUMNS:
					$detail_results = array('build_result', 'result_type', 'cr');
					$columns = count($detail_results);
					foreach($this->params['id'] as $cycle_id){
						if ($i ==  0){
							$h = array('label'=>$this->cycleInfo[$cycle_id]['name'], 'cols'=>$columns);
							$header[$i][] = $h;
						}
						else{
							foreach($detail_results as $field=>$r){
								$h = $this->getFieldHeader($field, $r);
								$h['index'] = 'cycle_'.$cycle_id.'_'.$r;
								// $h['hidden'] = $this->prjMerge;
								$subtotal[] = $h['index'];
								$header[$i][] = $h;
								$col ++;
							}
						}
					}
					if($this->prjMerge == 1){
						// $header[0][$col] = array('index'=>'final_result', 'label'=>'Final Result');
						// $header[1][] = array('index'=>'final_result', 'label'=>'Final Result');
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
			$res = $this->tool->query("SELECT id, $fields FROM $table WHERE id IN ($strIds)");
			while($row = $res->fetch()){
				$this->baseData[$table][$row['id']] = $row;
			}
		}
// print_r($this->baseData);
	}
	
	function testResultData(){
		$strIds = $this->strIds;
		$data = array();
		
		$base_table = array('codec_stream_format'=>'name', 'testcase_module'=>'name', 'test_env'=>'name', 
			'testcase_ver'=>'expected_result', 'prj'=>'name', 'compiler'=>'name', 'build_target'=>'name', 
			'build_result'=>'name', 'result_type'=>'name', 'cycle'=>'name');
		$base_ids = array();
		
		$case = 'testcase';
		$sql = "SELECT cycle_detail.cycle_id as cycle_id, cycle_detail.testcase_id, cycle_detail.testcase_ver_id, cycle_detail.codec_stream_id, cycle_detail.test_env_id, ".
			" cycle_detail.build_result_id, cycle_detail.result_type_id, cycle_detail.defect_ids, cycle_detail.finish_time,".
			" testcase.code as testcase, testcase.testcase_module_id,".
			" codec_stream.code as codec_stream, codec_stream.codec_stream_format_id, cycle.prj_id, cycle.compiler_id, cycle.build_target_id ".
			" FROM cycle_detail LEFT JOIN testcase on cycle_detail.testcase_id=testcase.id".
			" LEFT JOIN cycle on cycle.id=cycle_detail.cycle_id".
			" LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id".
			" WHERE cycle_detail.cycle_id IN ($strIds)".
			" ORDER BY codec_stream_format_id ASC, testcase_module_id ASC, codec_stream ASC, testcase ASC";
// print_r($sql);
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			foreach($base_table as $b=>$field){
				if (!empty($row[$b.'_id'])){
					if ($b == 'build_result')
						$base_ids['result_type'][$row[$b.'_id']] = $row[$b.'_id'];
					else
						$base_ids[$b][$row[$b.'_id']] = $row[$b.'_id'];
				}
			}
			
			$row['prj_id'] = $this->cycleInfo[$row['cycle_id']]['prj_id'];
			$row['compiler_id'] = $this->cycleInfo[$row['cycle_id']]['compiler_id'];
			$row['build_target_id'] = $this->cycleInfo[$row['cycle_id']]['build_target_id'];
			if ($row['codec_stream_id'] == 0){
				$type = 'normal';
				$module_id = $row['testcase_module_id'];
				
				$data['detail'][$type][$module_id][0][$row[$case]][$row['testcase_ver_id']][$row['prj_id']][$row['compiler_id']][$row['build_target_id']][$row['test_env_id']][$row['cycle_id']] = $row;
				if(!empty($row['finish_time']) && ($row['result_type_id'] == RESULT_TYPE_PASS || $row['result_type_id'] == RESULT_TYPE_FAIL))
					$data['detail'][$type][$module_id][0][$row[$case]]['__result__'][$row['finish_time']] = $row['result_type_id'];
			}
			else{
				$module_id = $row['codec_stream_format_id'];
				// $case = 'codec_stream';
				$type = 'codec_stream';
				$data['detail'][$type][$module_id][$row['codec_stream']][$row[$case]][$row['testcase_ver_id']][$row['prj_id']][$row['compiler_id']][$row['build_target_id']][$row['test_env_id']][$row['cycle_id']] = $row;
			}
		}
		
		$this->getBaseData($base_table, $base_ids);

		$this->handleDetailData($data['detail']);
	}
	
	protected function calcFinalResult(&$data){
		foreach(array('codec_stream', 'normal') as $type){
			if(!empty($data['prj_compiler_module'][$type])){
				foreach($data['prj_compiler_module'][$type] as $prj_id=>&$prj_data){
					foreach($prj_data as $compiler_id=>&$compiler_data){
						foreach($compiler_data as $module_id=>&$module_data){
							if($type == 'codec_stream'){
								$detailData = &$data['detail'][$type][$module_id];
							}
							else{
								$detailData = &$data['detail'][$type][$module_id][''];
							}
							$module_data['__final__']['total'] = count($detailData);
							// final_pass, final_fail, final_other, final_pass_rate
							// $data['detail'][$type][$module_id][$row['codec_stream']]['__result__'][$row['finish_time']]
							foreach($detailData as $codec_stream=>&$stream_data){
								$last_result = 0;
								if(!empty($stream_data['__result__'])){
									$finish_time = array_keys($stream_data['__result__']);
									$last_finish_time = max($finish_time);
									$last_result = $stream_data['__result__'][$last_finish_time];
								}
								$stream_data['__final_result__'] = $last_result;
								switch($last_result){
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
								if(empty($module_data['__final__'][$name]))
									$module_data['__final__'][$name] = 0;
								$module_data['__final__'][$name] ++;
							}
						}
					}
				}
			}
			if(!empty($data['module_prj_compiler'][$type])){
				foreach($data['module_prj_compiler'][$type] as $module_id=>&$module_data){
					if($type == 'codec_stream'){
						$detailData = $data['detail'][$type][$module_id];
					}
					else{
						$detailData = $data['detail'][$type][$module_id][''];
					}
					$module_data['__final__']['total'] = count($detailData);
					// final_pass, final_fail, final_other, final_pass_rate
					// $data['detail'][$type][$module_id][$row['codec_stream']]['__result__'][$row['finish_time']]
					foreach($detailData as $codec_stream=>$stream_data){
						$last_result = 0;
						if(!empty($stream_data['__result__'])){
							$finish_time = array_keys($stream_data['__result__']);
							$last_finish_time = max($finish_time);
							$last_result = $stream_data['__result__'][$last_finish_time];
						}
						$stream_data['__final_result__'] = $last_result;
						switch($last_result){
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
						if(empty($module_data['__final__'][$name]))
							$module_data['__final__'][$name] = 0;
						$module_data['__final__'][$name] ++;
					}
				}
			}
		}
	
	}
	
	protected function incTotal(&$data, $type, $row, $result){
		if($type == 'normal')
			$module_id = $row['testcase_module_id'];
		else	
			$module_id = $row['codec_stream_format_id'];
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
		$res = $this->tool->query($sql);
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
		$res = $this->tool->query($sql);
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
		$res = $this->tool->query($sql);
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
				$res = $this->tool->query("SELECT name from prj where id=$prj_id");
				$prj = $res->fetch();
				$prjs[$prj_id] = $prj['name'];
			}
			foreach($prj_data as $testcase_type_id=>$type_data){
				if (!isset($testcase_types[$testcase_type_id])){
					$res = $this->tool->query("select * from testcase_type where id=$testcase_type_id");
					$testcase_type = $res->fetch();
					$testcase_types[$testcase_type_id] = $testcase_type['name'];
				}
				foreach($type_data as $testcase_module_id=>$module_data){
					if (!isset($testcase_modules[$testcase_module_id])){
						$res = $this->tool->query("select * from testcase_module where id=$testcase_module_id");
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
							if($cycle_id == '__final__')continue;
							foreach($cycle_data as $result_type=>$result_count){
								$row['cycle_'.$cycle_id.'_'.$result_type] = $result_count;
							}
							$row['cycle_'.$cycle_id.'_pass_rate'] = !empty($row['cycle_'.$cycle_id.'_pass']) ? $row['cycle_'.$cycle_id.'_pass'] / $row['cycle_'.$cycle_id.'_total'] : 0;
						}
						if($this->prjMerge == 1){
// print_r($module_data);
							foreach(array('total', 'pass', 'fail', 'others') as $f){
								$row['final_'.$f] = isset($module_data['__final__'][$f]) ? $module_data['__final__'][$f] : 0;
							};
							$row['final_pass_rate'] = empty($row['final_total']) ? 0 : $row['final_pass'] / $row['final_total'];
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
// print_r("prj_id = $prj_id<<<<<\n");
// print_r($prj_data);
					if($prj_id == '__final__')continue;
					
					foreach($prj_data as $compiler_id=>$compiler_data){
						$row = array('prj'=>$this->baseData['prj'][$prj_id]['name'], 'compiler'=>$this->baseData['compiler'][$compiler_id]['name'], 
							'module'=>empty($this->baseData[$module][$module_id]['name']) ? '' : $this->baseData[$module][$module_id]['name']);
						foreach($compiler_data as $cycle_id=>$cycle_data){
							foreach($cycle_data as $result_type=>$result_count){
								$row['cycle_'.$cycle_id.'_'.$result_type] = $result_count;
							}
							$row['cycle_'.$cycle_id.'_pass_rate'] = !empty($row['cycle_'.$cycle_id.'_pass']) ? $row['cycle_'.$cycle_id.'_pass'] / $row['cycle_'.$cycle_id.'_total'] : 0;
						}
						if($this->prjMerge == 1){
// print_r($module_data);
							foreach(array('total', 'pass', 'fail', 'others') as $f){
								$row['final_'.$f] = isset($module_data['__final__'][$f]) ? $module_data['__final__'][$f] : 0;
							};
							$row['final_pass_rate'] = empty($row['final_total']) ? 0 : $row['final_pass'] / $row['final_total'];
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
						if($type == 'codec_stream' && ($testcase == '__result__' || $testcase == '__final_result__')){
							continue;
						}
						foreach($testcase_data as $ver_id=>$ver_data){
							if($type == 'normal' && ($ver_id == '__result__' || $ver_id == '__final_result__')){
								continue;
							}
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
											if($this->prjMerge == 1){
// print_r($codec_stream_data);
// print_r($testcase_data);							
												if($type == 'codec_stream')
													$row['final_result'] = empty($codec_stream_data['__final_result__']) ? '' : $this->baseData['result_type'][$codec_stream_data['__final_result__']]['name'];
												else
													$row['final_result'] = empty($testcase_data['__final_result__']) ? '' : $this->baseData['result_type'][$testcase_data['__final_result__']]['name'];
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
		if ($sheetIndex > $this->moduleSheet && !empty($v) && (stripos($headerIndex, 'result_type') !== false || stripos($headerIndex, 'build_result') !== false)){
			if (strtolower($v) != 'pass'){//RESULT_TYPE_PASS){
				$style = 'warning';
			}
		}
		return $style;
	}

};
?>
 