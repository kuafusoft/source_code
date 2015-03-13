<?php
require_once('dbfactory.php');
require_once('exporter_excel.php');

class xt_zzvw_cycle_detail_exporter_importresult extends exporter_excel{
	protected function getTitle($table_desc){
		$title = array();
		$exportFields = array(
			'id'=>array('hidden'=>true),
			'prj_id'=>array('hidden'=>true),
			'chip_id',
			'board_type_id',
			'os_id',
			'compiler_id',
			'build_target_id',
			'testcase_module_id'=>array('width'=>150, 'hidden'=>false),
			'd_code',
			'build_result_id'=>array('width'=>60, 'hidden'=>false),
			'result_type_id'=>array('width'=>60),
			'comment',
			'defect_ids'=>array('label'=>'CRID', 'hidden'=>false),
			'tester_id'=>array('hidden'=>false, 'width'=>80),
			'summary',
			'testcase_testpoint_id'=>array('hidden'=>true),
			'testcase_category_id'=>array('hidden'=>true),
			'testcase_priority_id'=>array('cols'=>1, 'hidden'=>false, 'width'=>60),
			'auto_level_id'=>array('hidden'=>true),
			'precondition'=>array('hidden'=>true, 'width'=>500),
			'objective'=>array('hidden'=>true, 'width'=>500),
			'steps'=>array('hidden'=>true, 'width'=>500)
		);
		$options = $table_desc->getOptions();
		$colModelMap = $options['gridOptions']['colModelMap'];
// print_r($colModelMap);
		$colModel = $options['gridOptions']['colModel'];
		foreach($exportFields as $k=>$v){
			if(is_int($k)){
				$k = $v;
				$v = array('hidden'=>false);
			}
			$orig = $colModel[$colModelMap[$k]];
			$title[] = array_merge($orig, $v);
		}
		return $title;
	}
};

?>
