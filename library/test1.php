<?php
	$a = array('a'=>'aa', 'b'=>'bb');
	foreach($a as &$k=>$v){
		$k = $k.$k;
	}
	print_r($a);
?>