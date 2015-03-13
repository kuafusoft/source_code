<?php
$fileName = $_REQUEST['filename'];
// print_r($_REQUEST);
if (isset($fileName)){
	$fileName = urldecode($fileName);
	$fileName = trim($fileName);
	$file = fopen($fileName,"rb"); 
	if (!$file){ 
		echo "Can not open file:" . $fileName; 
	} 
    else{ 
		echo fread($file, 1024);
		fclose ($file); 
	} 
}

?>
