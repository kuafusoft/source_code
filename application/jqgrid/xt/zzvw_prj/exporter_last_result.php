<?php
require_once('exporter_excel.php');
/*
应包含一些Sheets：
1. project-compiler-module情况
2. module-project-compiler情况
3. detail：testcase-project-compile情况
4. 各个project的情况

测试结果只显示Total、Pass、Fail and Others
如果选择了多个Release，则每个Release的情况并列
*/
class xt_zzvw_prj_exporter_last_result extends exporter_excel{
	protected $data = array();
	protected $db = null;
	protected $heads = array();
	protected $moduleSheet = 1;
	protected function init($params = array()){
		parent::init($params);
		$this->params['id'] = explode(',', $this->params['id']);
//print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_prj [export_type] => last_result [rel_ids] => Array ( [0] => 1 [1] => 2 ) [id] => 10,9 [real_table] => prj ) 
		$this->db = dbFactory::get($this->params['db']);
	}
	
	public function setOptions($jqgrid_action){
		$this->params['sheets'] = array(
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
		foreach($this->params['id'] as $prj_id){
			$this->params['sheets'][] = $this->getPrjSheet($jqgrid_action, $prj_id);
		}
	}
	
	protected function getPrjCompilerModuleSheet($jqgrid_action){
		$sheet = array('title'=>'Project-Compiler', 'startRow'=>2, 'startCol'=>1);
		$row0 = array('prj', 'compiler', 'module', 'total', 'pass', 'fail', 'others', 'pass_rate');
		$row1 = array('prj', 'compiler', 'module');
		$subtotalFields = array();
		foreach(array('total', 'pass', 'fail', 'others', 'pass_rate') as $result){
			foreach($this->params['rel_ids'] as $rel_id){
				$row1[] = 'rel.'.$rel_id.'.'.$result;
				$subtotalFields[] = 'rel_'.$rel_id.'_'.$result;
			}
		}
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'module'=>array(2, 3));
//print_r($sheet['header']);
		$sheet['data'] = $this->getData('prj_compiler_module');
		$sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), array('index'=>'compiler', 'subtotal'=>array('locate'=>'module', 'fields'=>$subtotalFields)));
//print_r($sheet);
		return $sheet;
	}
	
	protected function getCoverageSheet($jqgrid_action){
		$sheet = array('title'=>'Testcase Coverage', 'startRow'=>2, 'startCol'=>1, 'pre_text'=>"From {$this->params['coverage_begin']} to {$this->params['coverage_end']}");
		$row0 = array('prj', 'releases', 'cycles', 'testcase_type', 'testcase_module', 'p_1_3', 'p1', 'p2', 'p3');
		$row1 = array('prj', 'releases', 'cycles', 'testcase_type', 'testcase_module', 'total_1_3', 'runed_1_3', 'coverage_1_3', 'total_1', 'runed_1', 'coverage_1', 'total_2', 'runed_2', 'coverage_2', 'total_3', 'runed_3', 'coverage_3');
		$subtotalFields = array('total_1_3', 'runed_1_3', 'coverage_1_3', 'total_1', 'runed_1', 'coverage_1', 'total_2', 'runed_2', 'coverage_2', 'total_3', 'runed_3', 'coverage_3');
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'testcase_type'=>array(2, 3), 'testcase_module'=>array(2, 3), 'releases'=>array(2, 3), 'cycles'=>array(2, 3));
//print_r($sheet['header']);
		$sheet['data'] = $this->getCoverageData($this->params['coverage_begin'], $this->params['coverage_end']);
		$sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), array('index'=>'releases', 'subtotal'=>array()), array('index'=>'cycles', 'subtotal'=>array()), array('index'=>'testcase_type', 'subtotal'=>array('locate'=>'testcase_module', 'fields'=>$subtotalFields)));
//print_r($sheet);
		return $sheet;
	}
	
	protected function getModulePrjCompilerSheet($jqgrid_action){
		$sheet = array('title'=>'Module', 'startRow'=>2, 'startCol'=>1);
		$row0 = array('module', 'prj', 'compiler', 'total', 'pass', 'fail', 'others', 'pass_rate');
		$row1 = array('module', 'prj', 'compiler');
		$subtotalFields = array();
		foreach(array('total', 'pass', 'fail', 'others', 'pass_rate') as $result){
			foreach($this->params['rel_ids'] as $rel_id){
				$row1[] = 'rel.'.$rel_id.'.'.$result;
				$subtotalFields[] = 'rel_'.$rel_id.'_'.$result;
			}
		}
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array('prj'=>array(2, 3), 'compiler'=>array(2, 3), 'module'=>array(2, 3));
		$sheet['data'] = $this->getData('module_prj_compiler');
		$sheet['groups'] = array(array('index'=>'module', 'subtotal'=>array('locate'=>'prj', 'fields'=>$subtotalFields)), array('index'=>'prj'), array('index'=>'compiler'));
//print_r($sheet);
		return $sheet;
	}
	
	protected function getDetailSheet($jqgrid_action){
		$sheet = array('title'=>'Detail', 'startRow'=>2, 'startCol'=>1);
		$row0 = array('module', 'code', 'expected_result', 'prj', 'compiler');
		$row1 = array('module', 'code', 'expected_result', 'prj', 'compiler');
		foreach($this->params['rel_ids'] as $rel_id){
			$row0[] = 'rel.'.$rel_id;
			foreach(array('cycle', 'build_result_id', 'result_type_id', 'cr') as $r){
				$row1[] = $r.'.'.$rel_id.'.rel';
			}
		}
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array(
			'code'=>array(2, 3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 'prj'=>array(2, 3), 'compiler'=>array(2, 3)
		);
		$sheet['data'] = $this->getData('detail');
		$sheet['groups'] = array(array('index'=>'module'), array('index'=>'code'), array('index'=>'expected_result'));
		return $sheet;
	}
	
	protected function getPrjSheet($jqgrid_action, $prj_id){
		$res = $this->db->query("SELECT * FROM prj WHERE id=$prj_id");
		$row = $res->fetch();
		$sheet = array('title'=>$row['name'], 'startRow'=>2, 'startCol'=>1);
		$row0 = array('module', 'code', 'expected_result', 'compiler');
		$row1 = array('module', 'code', 'expected_result', 'compiler');
		foreach($this->params['rel_ids'] as $rel_id){
			$row0[] = 'rel.'.$rel_id;
			foreach(array('cycle', 'build_result_id', 'result_type_id', 'cr') as $r){
				$row1[] = $r.'.'.$rel_id.'.rel';
			}
//			$row1 = array_merge($row1, array('cycle', 'build_result_id', 'result_type_id', 'cr'));
		}
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array('code'=>array(2, 3), 'module'=>array(2, 3), 'expected_result'=>array(2, 3), 'compiler'=>array(2, 3));
		$sheet['data'] = $this->getData('prj_'.$row['name']);
		$sheet['groups'] = array(array('index'=>'module'), array('index'=>'code'), array('index'=>'expected_result'));
//		$sheet['groups'] = array('code'=>array(), 'module'=>array());
		return $sheet;
	}
	
	protected function getFieldHead($field, $jqgrid_action){
		if (empty($heads[$field])){
			$fields = explode('.', $field);
			$count = count($fields);
			switch($count){
				case 1:
					$index = $fields[0];
					$real_field = $fields[0];
					$label = $fields[0];
					break;
				case 2:// rel.01
					$index = implode('_', $fields);
					$real_field = $fields[0];
					$label = $fields[0];
					$id = $fields[1];
					break;
				case 3: // rel.01.pass
					$index = implode('_', $fields);
					$real_field = $fields[0];
					$label = $fields[0];
					$id = $fields[1];
					break;
			}
			$head = array('label'=>ucfirst($label), 'index'=>$index, 'width'=>100);
			switch($real_field){
				case 'prj':
					$head['label'] = 'Project';
					$head['width'] = 150;
					break;
				case 'total':
				case 'pass':
				case 'fail':
				case 'others':
					$head['cols'] = count($this->params['rel_ids']);
					break;
				case 'pass_rate':
					$head['cols'] = count($this->params['rel_ids']);
					$head['label'] = 'Pass Rate';
					$head['style'] = 'percent';
					break;
				case 'result_type_id':
				case 'build_result_id':
					$head['label'] = 'Result';
					if ($real_field == 'build_result_id')
						$head['label'] = 'Build Result';
					$head['formatter'] = $head['edittype'] = 'select';
					$head['editable'] = true;
					$head['editrules']['required'] = true;
					$jqgrid_action->getTable_desc()->fillOptions($head, $this->params['db'], 'result_type');
					break;
				case 'rel':
					$res = $this->db->query("SELECT * FROM rel WHERE id=$id");
					$row = $res->fetch();
					$head['label'] = $row['name'];
					if ($count == 2)
						$head['cols'] = 4;
					else if ($count == 3 && $fields[2] == 'pass_rate')
						$head['style'] = 'percent';
					break;
				case 'expected_result':
					$head['width'] = 300;
					break;
				case 'code':
					$head['label'] = 'Testcase';
					break;
				case 'total_1_3':
				case 'total_1':
				case 'total_2':
				case 'total_3':
					$head['label'] = 'Total';
					break;
				case 'runed_1_3':
				case 'runed_1':
				case 'runed_2':
				case 'runed_3':
					$head['label'] = 'Run';
					break;
				case 'coverage_1_3':
				case 'coverage_1':
				case 'coverage_2':
				case 'coverage_3':
					$head['label'] = 'Execute Rate';
					$head['style'] = 'percent';
					break;
				case 'testcase_type':
					$head['label'] = 'Testcase Type';
					break;
				case 'p_1_3':
					$head['label'] = 'P1-P3';
				case 'p1':
				case 'p2':
				case 'p3':
					$head['label'] .= ' Cases';
					$head['cols'] = 3;
					break;
				case 'testcase_module':
					$head['label'] = 'Testcase Module';
					break;
				case 'releases':
				case 'cycles':
					$head['hidden'] = true;
					break;
			}
			$heads[$field] = $head;
		}
		return $heads[$field];
	}
	
	protected function getData($sheet, $searchConditions = array(), $order = array()){
		if (empty($this->data)){
			$data = array();
			// 先获取具体的数据
			$condition = array(
				array('field'=>'prj_id', 'op'=>'in', 'value'=>$this->params['id']),
				array('field'=>'rel_id', 'op'=>'in', 'value'=>$this->params['rel_ids'])
			);
			$last_result = tableDescFactory::get('xt', 'testcase_last_result');
			$components = $last_result->calcSqlComponents(array('db'=>'xt', 'table'=>'testcase_last_result', 'searchConditions'=>$condition), false);
			$sql = $last_result->getSql($components);
			$res = $this->db->query($sql);
			while($row = $res->fetch()){
				$row = $last_result->getMoreInfoForRow($row);
				$data['detail'][$row['code']][$row['module']][$row['prj']][$row['compiler']][$row['rel_id']] = $row;
				if (empty($data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']]['rel_'.$row['rel_id'].'_total']))
					$data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']]['rel_'.$row['rel_id'].'_total'] = 1;
				else
					$data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']]['rel_'.$row['rel_id'].'_total'] ++;
				if (empty($data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']]['rel_'.$row['rel_id'].'_total']))
					$data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']]['rel_'.$row['rel_id'].'_total'] = 1;
				else
					$data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']]['rel_'.$row['rel_id'].'_total'] ++;
				switch($row['result_type_id']){
					case RESULT_TYPE_PASS:
						$name = 'rel_'.$row['rel_id'].'_pass';
						break;
					case RESULT_TYPE_FAIL:
						$name = 'rel_'.$row['rel_id'].'_fail';
						break;
					default:
						$name = 'rel_'.$row['rel_id'].'_others';
						break;
				}
				if (empty($data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']][$name]))
					$data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']][$name] = 1;
				else
					$data['prj_compiler_module'][$row['prj']][$row['compiler']][$row['module']][$name] ++;
				if (empty($data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']][$name]))
					$data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']][$name] = 1;
				else
					$data['module_prj_compiler'][$row['module']][$row['prj']][$row['compiler']][$name] ++;
			}
			$this->handlePrjCompilerModuleData($data['prj_compiler_module']);
			$this->handleModulePrjCompilerData($data['module_prj_compiler']);
			$this->handleDetailData($data['detail']);
		}
		return $this->data[$sheet];
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
		$this->data['prj_compiler_module'] = array();
//print_r($data);		
		$i = 0;
		foreach($data as $prj=>$prj_data){
			foreach($prj_data as $compiler=>$compiler_data){
				foreach($compiler_data as $module=>$module_data){
					$this->data['prj_compiler_module'][$i] = array('prj'=>$prj, 'compiler'=>$compiler, 'module'=>$module);
					foreach($module_data as $result_type=>$result_count){
						$this->data['prj_compiler_module'][$i][$result_type] = $result_count;
					}
					// calculate the passrate
					if (preg_match('/^(.*?_\d*)/', $result_type, $matches)){
						if ($this->data['prj_compiler_module'][$i][$matches[1].'_total'] && !empty($this->data['prj_compiler_module'][$i][$matches[1].'_pass'])){
							$this->data['prj_compiler_module'][$i][$matches[1].'_pass_rate'] = 
								$this->data['prj_compiler_module'][$i][$matches[1].'_pass'] /$this->data['prj_compiler_module'][$i][$matches[1].'_total'];
						}
						else
							$this->data['prj_compiler_module'][$i][$matches[1].'_pass_rate'] = 0;
					}
					$i ++;
				}
			}
		}
	}
	
	protected function handleModulePrjCompilerData($data){
		$this->data['module_prj_compiler'] = array();
		$i = 0;
		foreach($data as $module=>$module_data){
			foreach($module_data as $prj=>$prj_data){
				foreach($prj_data as $compiler=>$compiler_data){
					$this->data['module_prj_compiler'][$i] = array('prj'=>$prj, 'compiler'=>$compiler, 'module'=>$module);
					foreach($compiler_data as $result_type=>$result_count){
						$this->data['module_prj_compiler'][$i][$result_type] = $result_count;
					}
					// calculate the passrate
					if (preg_match('/^(.*?_\d*)/', $result_type, $matches)){
						if ($this->data['module_prj_compiler'][$i][$matches[1].'_total'] && !empty($this->data['module_prj_compiler'][$i][$matches[1].'_pass'])){
							$this->data['module_prj_compiler'][$i][$matches[1].'_pass_rate'] = 
								$this->data['module_prj_compiler'][$i][$matches[1].'_pass'] /$this->data['module_prj_compiler'][$i][$matches[1].'_total'];
						}
						else
							$this->data['module_prj_compiler'][$i][$matches[1].'_pass_rate'] = 0;
					}
					$i ++;
				}
			}
		}
	}
	
	protected function handleDetailData($data){
//				$data['detail'][$row['code']][$row['module']][$row['prj_id']][$row['compiler']][$row['rel_id']] = $row;
		$this->data['detail'] = array();
		$i = 0;
		foreach($data as $code=>$code_data){
			foreach($code_data as $module=>$module_data){
				foreach($module_data as $prj=>$prj_data){
//print_r("prj = $prj\n");				
					foreach($prj_data as $compiler=>$compiler_data){
						foreach($compiler_data as $rel_id=>$rel_data){
							foreach(array('cycle', 'build_result_id', 'result_type_id', 'cr') as $r){
								$rel_data[$r.'_'.$rel_id.'_rel'] = $rel_data[$r];
								unset($rel_data[$r]);
							}
							$this->data['detail'][] = $rel_data;
							if (empty($this->data['prj_'.$prj])){
								$this->data['prj_'.$prj] = array();
							}
							$this->data['prj_'.$prj][] = $rel_data;
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
 