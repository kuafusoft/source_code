<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_script extends action_jqgrid{

	protected function handlePost(){
		$params = $this->parseParams();
//		print_r($params);
		$ret = '';
        $rename = 'cycle_'.$params['id'][0];
		$res = $this->db->query("SELECT name FROM cycle WHERE id=".$params['id'][0]);
		$cycle_info = $res->fetch();
        if ($cycle_info){
            $rename = str_replace('/', '_', $cycle_info['name']);
            $rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
            $realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
            $download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
        	$sql = "SELECT * FROM zzvw_cycle_detail".
        		" WHERE cycle_id =".$params['id'][0]." AND auto_level_id=".$params['script_type'];
        	$result = $this->db->query($sql);
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