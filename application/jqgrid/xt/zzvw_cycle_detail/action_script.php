<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_script extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		$ret = '';
		//$params = $this->parseParams();
		$params = $this->params;
		$element = $this->caclIDs($params);
		if($element == "error")
			return "error";
		
        $rename = "cycle_detail".implode("_", $element);//会导致name过长，怎么解决
		$rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
		$realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
		$download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
		$sql = "SELECT * FROM ".$this->get('table')." WHERE id in (".implode(",", $element).") AND auto_level_id=".$params['script_type'];
		$result = $this->tool->query($sql);
		$str = '';
		while ($row = $result->fetch()){
			if(!empty($row["command"]))
				$str .= $row["testcase_id"] . " " . $row["command"] . "\n";
		}
		if ($str != ''){
			$handle = fopen($realFileName, 'wb');
			if ($handle){
				if (fwrite($handle, $str)){
					fclose($handle);
					$ret = json_encode($download);
				}
			}
		}
        return $ret;
	}
	
}

?>