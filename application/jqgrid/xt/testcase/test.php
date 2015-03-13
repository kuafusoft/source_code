<?php
	// test xml
	require_once('Zend/Config/xml.php');
	$config = new Zend_Config_Xml('access.xml'); 
// print_r($config);
	$menu = $config->toArray();
print_r($menu);

?>