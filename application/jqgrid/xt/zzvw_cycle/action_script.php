<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_script extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file'] = 'script.phtml';
		$view_params['view_file_dir'] = '/jqgrid/xt/zzvw_cycle';
		return $view_params;
	}
	
	protected function handlePost(){
		$params = $this->parseParams();
//		print_r($params);
		$ret = '';
        $rename = 'cycle_'.$params['id'];
		$res = $this->tool->query("SELECT name FROM cycle WHERE id=".$params['id']);
		$cycle_info = $res->fetch();
// print_r($cycle_info);		
        if ($cycle_info){
            $rename = str_replace('/', '_', $cycle_info['name']);
            $rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
            $realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
            $download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
        	$sql = "SELECT * FROM zzvw_cycle_detail".
        		" WHERE cycle_id =".$params['id']." AND auto_level_id=".$params['script_type'];
        	$result = $this->tool->query($sql);
            $str = '';
        	while ($row = $result->fetch()){
				if(!empty($row["command"]))
					$str .= $row["testcase_id"] . " " . $row["command"] . "\n";
        	}
            if (!empty($str)){
                $handle = @fopen($realFileName, 'wb');
                if ($handle){
                    if (fwrite($handle, $str)){
                        fclose($handle);
                        $ret = json_encode($download);
                    }
                }
            }
        }
        return $ret;
	}
}
?>