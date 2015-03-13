<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_log_delete extends action_jqgrid{

	public function handlePost(){
		$field = "logFile";
		$sql = "select cycle_id, logFile from cycle_detail where id = {$this->params['id']}";
		if($this->params['table'] == 'zzvw_cycle_detail_stream'){
			$field = "stream_logFile";
			$sql = "select cycle_id, stream_logFile as logFile from cycle_detail where id = {$this->params['id']}";
		}
		$res = $this->tool->query($sql);
		$data = $res->fetch();
		$file_name = explode(" ", $this->params['fileName']);
		
		$path = APPLICATION_PATH."/log/".$data['cycle_id']."/".$this->params['id'];
		if($this->params['table'] == 'zzvw_cycle_detail_stream')
			$path = APPLICATION_PATH."/log/".$data['cycle_id']."/".$this->params['id']."/stream";
		$path = $this->tool->uniformFileName($path);
		$fileName = $path."/".$file_name[0];
		$fileName = $this->tool->uniformFileName($fileName);
		if(!file_exists($fileName)){
			return;
		}
			
		if (PHP_OS == 'Linux'){
			$cmd = 'chmod -R a+r '.$fileName;
			$line = exec($cmd, $output, $retVal);	
			if ($retVal){ // failed
				print_r("chmod retVal = $retVal");
				return;
			}
			$cmd = 'rm -f '.$fileName;
			$line = exec($cmd, $output, $retVal);	
			if ($retVal){ // failed
				print_r("rm retVal = $retVal");
				return;
			}
		}
		$fileList = explode(";", $data['logFile']);
		$key = array_search($this->params['fileName'], $fileList);
		if($key !== false){
			unset($fileList[$key]);
			$this->tool->update("cycle_detail", array($field=>implode(";", $fileList)), "id=".$this->params['id']);
			return json_encode(array('id'=>$this->params['id'], 'logFile'=>implode(";", $fileList)));
		}
	}
}
?>