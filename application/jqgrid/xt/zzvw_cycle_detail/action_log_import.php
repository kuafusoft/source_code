<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_log_import extends action_jqgrid{

	public function handlePost(){
		$sql = "select cycle_id, logFile from cycle_detail where id = {$this->params['id']}";
		$field = 'logFile';
		
		if('zzvw_cycle_detail_stream' == $this->params['table']){
			$sql = "select cycle_id, stream_logFile as logFile from cycle_detail where id = {$this->params['id']}";
			$field = 'stream_logFile';
		}
// print_r($this->params);
		$res = $this->tool->query($sql);
		$data = $res->fetch();
		$file_name = $this->tool->uniformFileName($_FILES['logfile']["name"]);
		$fileName = APPLICATION_PATH."/log/".$data['cycle_id']."/".$this->params['id']."/".$file_name;
		
		if('zzvw_cycle_detail_stream' == $this->params['table'])
			$fileName = APPLICATION_PATH."/log/".$data['cycle_id']."/".$this->params['id']."/stream/".$file_name;
			
		$fileName = $this->tool->uniformFileName($fileName);
		$path_parts = pathinfo($fileName);
		$this->tool->createDirectory($path_parts['dirname']);
		move_uploaded_file($_FILES['logfile']["tmp_name"], $fileName);
		if(file_exists($fileName)){
			$fileSize = filesize($fileName);
			if(empty($data['logFile']))
				$update = array($field=>$file_name." ".$fileSize);
			else{
				$logs = explode(";", $data['logFile']);
				if(!in_array($file_name." ".$fileSize, $logs))
					$update = array($field=>$data['logFile'].";".$file_name." ".$fileSize);
			}
// print_r($update);
			if(!empty($update))
				$this->tool->update("cycle_detail", $update, "id=".$this->params['id']);
			print_r("log uplod successfully!!!");
			print_r("<input id='logfile_path' type='hidden' name='logfile_path' value='".$fileName."'>");
			if (PHP_OS == 'Linux'){
				$cmd = 'chmod -R a+r '.$fileName;
				$line = exec($cmd, $output, $retVal);	
				if ($retVal){ // failed
					print_r("chmod retVal = $retVal");
					return;
				}
			}
		}	
	}
}
?>