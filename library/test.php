<?php
require_once('importer_stream.php');

$params = array(
	'db'=>'xt',
	'table'=>'codec_stream',
	'config_file'=>'stream.config.php'
);
$importer = new importer_stream($params);

$importer->import();

?>