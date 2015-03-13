<?php
$fileName = $_REQUEST['filename'];
//$fileName = realpath($fileName);
$rename = isset($_REQUEST['rename']) ? $_REQUEST['rename'] : $fileName;
$remove = isset($_REQUEST['remove']) ? $_REQUEST['remove'] : false;
if (isset($fileName)){
	$fileName = trim($fileName);
	$file = fopen($fileName,"rb"); 
	if (!$file){ 
		echo "Can not open file:" . $fileName; 
	} 
    else{ 
		Header("Content-type: application/octet-stream");
		Header("Content-Disposition: attachment; filename=\"".basename($rename)."\"");
		while (!feof ($file)) { 
			echo fread($file,500000); 
		} 
		fclose ($file); 
		if ($remove)
		  unlink($fileName);
	} 
}

?>
