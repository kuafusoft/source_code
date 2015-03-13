<?php
require_once(APPLICATION_PATH.'/jqgrid/action_import.php');

class xt_zzvw_cycle_detail_action_import extends action_import{
	protected function handlePost(){
		$params = $this->params;
print_r($params);
		$sql = "SELECT cycle.id as cycle_id, cycle.name as cycle, detail.codec_stream_id, stream.streamid as streamid, testcase.code as code".
			" FROM cycle_detail detail".
			" LEFT JOIN cycle ON detail.cycle_id=cycle.id".
			" LEFT JOIN testcase ON detail.testcase_id=testcase.id".
			" LEFT JOIN codec_stream stream ON detail.codec_stream_id=stream.id".
			" WHERE detail.id=".$params['id'];
		$res = $this->tool->query($sql);
		if($info = $res->fetch()){
			$logFile = $params['cellName'];//选择文件的那个input的id号
			$strLogFilePath = LOG_ROOT;
			if (isset($_FILES[$logFile])){
				if(!empty($info['codec_stream_id']))
					$path = LOG_ROOT."/".$info['cycle'].'_'.$info['cycle_id']."/".$info['streamid']."/".$info['code'].'_'.$params['id'];
				else
					$path = LOG_ROOT."/".$info['cycle'].'_'.$info['cycle_id']."/".$info['code'].'_'.$params['id'];
				if (!file_exists($strLogFilePath)){
					if (PHP_OS == "WINNT"){
						mkdir($strLogFilePath, 0700, true);
					}
					else{
						system('/bin/mkdir -p '.escapeshellarg($strLogFilePath) . ' -m 777');
					}
				}
				if(!file_exists($path)){
					if (PHP_OS == "WINNT"){
						mkdir($path, 0700, true);
					}
					else{
						system('/bin/mkdir -p '.escapeshellarg($path) . ' -m 777');
					}
				}
				$path .= "/".str_replace(' ', '', basename($_FILES[$logFile]['name']));
				move_uploaded_file($_FILES[$logFile]["tmp_name"], $path);
				if(file_exists($path)){
					if (PHP_OS == "Linux"){//"LINUX"
						$cmd = 'chmod -R a+r '. $path;
						$line = exec($cmd, $output, $retVal);	
						if ($retVal){ // failed
							print_r("chmod retVal = $retVal");
							print_r($output);
							return;
						}
					}
					return "upload successully";//怎么给出提示
				}
			}
		}
	}
	
}

?>