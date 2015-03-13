<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getlogfile extends action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$res = $this->tool->query("SELECT testcase.code as code, cycle.name as cycle, detail.cycle_id as cycle_id FROM cycle_detail detail".
		" LEFT JOIN testcase ON testcase.id=detail.testcase_id".
		" LEFT JOIN cycle ON cycle.id=detail.cycle_id".
		" WHERE detail.id=".$params['id']);
		$info = $res->fetch();
		$path = LOG_ROOT."/".$info['cycle']."_".$info['cycle_id']."/".$info['code']."_".$params['id'];
		$str = '';
		$content = array();
		if(is_dir($path)){
			if($dir = opendir($path)){
				$str .= "<div id='logfiles_{$params['id']}' style='width:800px;font-size:12px'><ul>";
				$i = 1;
				while(($file = readdir($dir)) != false){
					if ($file != "." && $file != "..") {
						$filename = $path."/".$file;
						if ($handle = fopen($filename, 'rb')){
							if($ct = fread($handle, filesize($filename))){
								$str .= "<li><a href='#logfiles_{$params['id']} #aaa{$i}'>{$file}</a></li>";
								$content['aaa'.$i] = $ct;
								$i++;
							}
						}	
					}
				}
				$str .= '</ul>';
			}
		}
		if(empty($content))
			$str .= "<div id='nolog' style='width:750px;height:100px; overflow-y:scroll; border:1px solid;font-size:16px'><fieldset>No logfile here !</fieldset></div>";
		else{
			foreach($content as $key=>$val){
				$str .= "<div id='{$key}' style='width:750px;height:100px; overflow-y:scroll; border:1px solid;font-size:15px'><fieldset><pre>{$val}</pre></fieldset></div>";
			}
		}
		$str .= '</div>';
		return $str;
	}
	
}

?>