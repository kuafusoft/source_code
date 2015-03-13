<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_set_result.php');

class xt_zzvw_subgrid_cycle_detail_action_set_result extends xt_zzvw_cycle_detail_action_set_result{
	protected function returnData($data){
		// $datas = array('subData'=>$data);
		
		$res = $this->db->query("select cycle_id, codec_stream_id, test_env_id from cycle_detail where id=".$data['id']);
		if($info = $res->fetch()){
			$res0 = $this->db->query("select id from cycle_detail where codec_stream_id = (".$info['codec_stream_id'].
				") AND cycle_id = ".$info['cycle_id']." AND test_env_id = ".$info['test_env_id']);
			while($row = $res0->fetch()){
				$element[] = $row['id'];
			}
		}
		$res = $this->db->query("select distinct result_type_id from cycle_detail where id in (".implode(",", $element).")");
		while($info = $res->fetch()){
			$results[] = $info['result_type_id'];
		}
		
		$res = $this->db->query("SELECT id, name FROM result_type");
		$result_type[0] = 'Blank';
		while($info = $res->fetch()){
			$result_type[$info['id']] = $info['name'];
		}
	
		if(count(array_unique($results)) == 1){
			$total['result_type_id'] = $results[0];
			$total['codec_stream_result'] = 'All '.$result_type[$results[0]];
		}
		else{
			unset($result_type[1]);
			foreach($result_type as $k=>$v){
				if(in_array($k, $results)){
					$total['result_type_id'] = 100 + $k;// Testing
					$total['codec_stream_result'] = 'Has '.$v;
					if(in_array(1, $results))
						$total['codec_stream_result'] = 'Has '.$v.' & Pass';
					if($k != '2'){
						if(in_array(2, $results))
							$total['codec_stream_result'] = 'Has '.$v.' & Fail';
					}
					if($k == 0 || $k = 2)
						break;
				}
			}
		}
		$total['finish_time'] = $data['finish_time'];
		//$total['id'] = $data['id'];
		$datas['datas'] = $total;
		return json_encode($datas);
		
	}
	
}

?>