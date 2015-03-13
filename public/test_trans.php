<?php
	require_once("../library/transXT.php");
	
	$tables = array(
		// 'tms_pub_os'=>array('target'=>'os', 'fields'=>array('id', 'name', 'isactive')),
		// 'tms_pf_platform'=>array('target'=>'platform', 'fields'=>array('id', 'name')),
		// 'tms_tc_type'=>array('target'=>'testcase_type', 'fields'=>array('id', 'name')),
		// 'tms_pf_module'=>array('target'=>'testcase_module', 'fields'=>array('id', 'name', 'description', 'isactive', 'typeid'=>'testcase_type_ids', 'createtime'=>'created', 'creatorid'=>'creater_id')),
		// 'tms_pf_testpoint'=>array('target'=>'testcase_testpoint', 'fields'=>array('id', 'name', 'description', 'moduleid'=>'testcase_module_id', 'isactive')),
		// 'tms_tc_priority'=>array('target'=>'testcase_priority', 'fields'=>array('id', 'name')),
		// 'tms_tc_category'=>array('target'=>'testcase_category', 'fields'=>array('id', 'name')),
		// 'tms_tc_source'=>array('target'=>'testcase_source', 'fields'=>array('id', 'name')),
		// 'tms_tc_rule'=>array('target'=>'parse_rule', 'fields'=>array('id', 'name')),
		// 'tms_tc_status'=>array('target'=>'edit_status', 'fields'=>array('id', 'name')),
		// 'tms_tc_testcase'=>array('target'=>'testcase', 'fields'=>array()),
		// 'tms_tc_version'=>array('target'=>'testcase_ver', 'fields'=>array()),
		// 'tms_tc_platform_link'=>array('target'=>'prj_testcase_ver', 'fields'=>array('id', 'name')),
		// 'tms_tc_platform_history'=>array('target'=>'prj_testcase_ver_history', 'fields'=>array('id', 'name')),
		
		// 'tms_tr_status'=>array('target'=>'cycle_status', 'fields'=>array('id', 'name')),
		// 'tms_tr_resulttype'=>array('target'=>'result_type', 'fields'=>array('id', 'name', 'description')),
		// 'tms_tr_type'=>array('target'=>'cycle_type', 'fields'=>array('id', 'name', 'description')),
		// 'tms_tr_summary'=>array('target'=>'cycle', 'fields'=>array()),
		// 'tms_tr_detail'=>array('target'=>'cycle_detail', 'fields'=>array()),
	);
	
	$umbrella = array('host'=>'10.192.225.199', 'username'=>'yy', 'password'=>'', 'dbname'=>'xiaotian');
	$xt_1 = array('host'=>'localhost', 'username'=>'root', 'password'=>'dbadmin', 'dbname'=>'xt_1');
	$t = new transXT($umbrella, $xt_1);


	// $t->empty_table('chip');
	// $t->empty_table('chip_type');
	// $t->empty_table('board_type');
	
// print_r("lasjldjljdf");
	// return;
	
	foreach($tables as $source_table=>$table){
		$t->empty_table($table['target']);
		$fields = isset($table['fields']) ? $table['fields'] : array();
		$t->trans_table($source_table, $table['target'], $fields);
		// $map = $t->getMap($table['target']);
		// print_r(">>>>>>>>target = {$table['target']}, map = ");
		// print_r($map);
		// print_r("<<<<<<<<<<<<<<<<\n");
	}
	
	print_r("finished");
?>