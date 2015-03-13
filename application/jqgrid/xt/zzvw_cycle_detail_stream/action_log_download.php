<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_stream_action_log_download extends action_jqgrid{
	public function handlePost(){
		$res = $this->tool->query("select cycle.name as cycle, cycle.id as cycle_id, codec_stream.code as code, codec_stream.id as codec_stream_id".
			" from cycle_detail left join cycle".
			" on cycle.id = cycle_detail.cycle_id left join codec_stream on codec_stream.id = cycle_detail.codec_stream_id".
			" where cycle_detail.id = ".$this->params['id']);
		$data = $res->fetch();
		$fdir = APPLICATION_PATH."/log/".$data['cycle']."_".$data['cycle_id']."/".$data['code']."_".$this->params['id'];
		if(file_exists($fdir)){
			$dirs = scandir($fdir);
			foreach($dirs as $dir){
				if($dir == "." || $dir = "..")
					continue;
				$file_name = $dir;
				break;
			}
			if(!empty($file_name))
				if(file_exists($fdir."/".$file_name))
					return  $fdir."/".$file_name;
		}
	}
}
?>