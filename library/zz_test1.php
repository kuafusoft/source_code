<?php
	require_once('kf_form.php');
	
	$elements = array(
		// array(
			// 'id'=>'name', 'value'=>'Ye Yongli http://www.sina.com.cn, mailto:yy@ff.com ', 'required'=>true, 'unique'=>true, 'type'=>'text'
		// ),
		// array(
			// 'id'=>'name2', 'value'=>'Ye Yongli http://www.sina.com.cn, mailto:yy@ff.com ', 'required'=>true, 'unique'=>true, 'type'=>'textarea'
		// ),
		// array(
			// 'id'=>'age', 'value'=>'21', 'defval'=>20, 'required'=>true, 'type'=>'text', 'DATA_TYPE'=>'int', 'min'=>0, 'max'=>150, 'post'=>array('value'=>'岁')
		// ),
		// array(
			// 'id'=>'password', 'value'=>'It is the password', 'defval'=>'', 'required'=>true, 'type'=>'password', 'DATA_TYPE'=>'varchar', 'post'=>array('value'=>'年年岁岁')
		// ),
		// array(
			// 'id'=>'date', 'value'=>'2005-11-1', 'defval'=>'', 'required'=>true, 'type'=>'date', 'DATA_TYPE'=>'date', 'post'=>array('value'=>'年年岁岁')
		// ),
		// array(
			// 'id'=>'gender', 'value'=>1, 'defval'=>1, 'type'=>'select', 'editoptions'=>array('multiple'=>false, 'value'=>array(1=>'Male', 2=>'Female'))
		// ),
		// array(
			// 'id'=>'gender1', 'value'=>'1,2', 'defval'=>1, 'type'=>'select', 'editoptions'=>array('multiple'=>true, 'value'=>array(1=>'Male', 2=>'Female'))
		// ),
		// array(
			// 'id'=>'group', 'value'=>2, 'defval'=>1, 'type'=>'select', 'editoptions'=>array('value'=>array(1=>array('name'=>'A', 'note'=>'jlasjdfldskjf', 'disabled'=>1), 2=>'B', 'C', 'D', 'E', 'F', 'G', 'H'))
		// ),
		array(
			'id'=>'role', 'value'=>'1,2', 'defval'=>1, 'type'=>'cart', 'cart_db'=>'useradmin', 'cart_table'=>'roles',
			'editoptions'=>array('value'=>array(1=>array('name'=>'A', 'note'=>'It is a test', 'disabled'=>'disabled', 'readonly'=>'readonly'), 2=>'B', 'C', 'D', 'E', 'F', 'G', 'H'))
		),
		array(
			'id'=>'single_multi', 'value'=>'2', 'defval'=>1, 'type'=>'single_multi', 'cart_db'=>'useradmin', 'cart_table'=>'roles',
			'editoptions'=>array('value'=>array(1=>array('name'=>'A', 'note'=>'It is a test', 'disabled'=>'disabled', 'readonly'=>'readonly'), 2=>'B', 'C', 'D', 'E', 'F', 'G', 'H'))
		),
		array(
			'id'=>'multi', 'value'=>'1,2,4,5,6,8', 'defval'=>1, 'type'=>'single_multi', 'init_type'=>'multi', 'cart_db'=>'useradmin', 'cart_table'=>'roles',
			'editoptions'=>array('value'=>array(1=>array('name'=>'A', 'note'=>'It is a test', 'disabled'=>'disabled', 'readonly'=>'readonly'), 2=>'B', 'C', 'D', 'E', 'F', 'G', 'H'))
		),
		
		// array(
			// 'id'=>'dir', 'value'=>'C:\\Users\\b19268\\xampp\\kuafu\\library', 'type'=>'dir', 'editable'=>true, 'rel'=>''
		// ),
		array(
			'id'=>'prj_id', 'db'=>'xt', 'table'=>'prj', 'type'=>'auto_complete', 'editable'=>true
		),
	);
	
	$form = new kf_form($elements, array('gender'=>'1', 'gender1'=>"1,2", 'role'=>'1,2,3'), DISPLAY_STATUS_QUERY);
	// $form = new kf_form($elements, array('gender'=>'1', 'gender1'=>"1,2", 'role'=>'1,2,3'), DISPLAY_STATUS_VIEW);
	$ret = $form->display(1);
	print_r($ret);
?>