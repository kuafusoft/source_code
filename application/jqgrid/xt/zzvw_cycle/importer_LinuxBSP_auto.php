<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle/importer_codec_apollo_gvb.php');

class xt_zzvw_cycle_importer_LinuxBSP_auto extends xt_zzvw_cycle_importer_codec_apollo_gvb{
	protected $total = 0;
	
	protected function parse($fileName){
		$str = '';
		$handle = fopen($fileName, 'r');
		if ($handle){
			while(!feof($handle))
			  $data[] = fgets($handle);
			fclose($handle);
		}
		if(!empty($data)){
			for($row=0; $row<count($data); $row++){
				$row_data = trim($data[$row]);
				if(preg_match("/^Test Start Time:\s(.*?)\s(.*?)(\d{1,2})\s(\d{2}):(\d{2}):(\d{2})\s(\d{4})$/", $row_data , $mc)){
					$timestamp = strtotime($mc[3]." ".$mc[2]." ".$mc[7]." ".$mc[4].":".$mc[5].":".$mc[6]);
					$timestamp = date("Y-m-d H:i:m", $timestamp);
// print_r("\n<BR />");
				}
				else if(preg_match("/^(.*?)\s{1,}(.*?)\s{1,}(\d+)$/", $row_data , $matches)){//(.*)\s(?=\s)(.*)\s(?=\s)(\d)
					$this->parse_result[$timestamp][$row]['code'] = $matches[1];
					$this->parse_result[$timestamp][$row]['result'] = $matches[2];
				}					
			}
		}
	}
	
	protected function process(){
		if(!empty($this->parse_result)){
			$auto = $update_auto = 0;
			$result_na = $this->getResultId('na');
			foreach($this->parse_result as $stamp=>$row_data){
// print_r($row_data );
// print_r("\n<BR />");
				if($stamp == 0)
					$stamp = date('Y-m-d H:i:s');
				foreach($row_data as $detail){
					$testcase_id = $this->getId('testcase', array('code'=>trim($detail['code'])), array('code'));
					if($testcase_id != 'error'){
						if(strtolower($detail['result']) != 'pass')
							$detail['result'] = 'NA';
						$result_type_id = $this->getResultId($detail['result']);
						if('error' == $result_type_id || 0 == $result_type_id)
							continue;
						$data =  array('result_type_id'=>$result_type_id, 'finish_time'=>$stamp, 'comment'=>'auto test result', 'tester_id'=>$this->params['owner_id']);
						$cond = "cycle_id=".$this->params['id']." AND testcase_id = ".$testcase_id.
								" AND test_env_id=".$this->params['test_env_id']." AND codec_stream_id = 0";
						$res = $this->tool->query("select id, result_type_id, comment from cycle_detail where {$cond}");
						if($row = $res->fetch()){
							if($row['result_type_id'] == 0 || $row['result_type_id'] == $result_na){
								$this->tool->update('cycle_detail', $data, "id=".$row['id']);
								$this->updatelastresult($testcase_id, $this->params['id'], $result_type_id, $stamp);
								$auto ++;
							}
							else {
								if($row['comment'] == 'auto test result'){
									$update_auto ++;
								}
							}
						}
					}
				}				
			}
			if(!empty($auto))
print_r($auto." cases have updated at first time"."\n<br />");
			if(!empty($update_auto))
print_r($update_auto." cases have been update"."\n<br />" );
		}
	}
};

?>
