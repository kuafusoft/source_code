<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_saveOneResult.php');

class xt_zzvw_subgrid_cycle_detail_action_saveOneResult extends xt_zzvw_cycle_detail_action_saveOneResult{

	protected function returnData($data){
		$res = $this->db->query("select cycle_id, codec_stream_id, test_env_id from cycle_detail where id=".$data['id']);
		if($info = $res->fetch()){
			$res0 = $this->db->query("select id from cycle_detail where codec_stream_id = (".$info['codec_stream_id'].
				") AND cycle_id = ".$info['cycle_id']." AND test_env_id = ".$info['test_env_id']);
			while($row = $res0->fetch()){
				$element[] = $row['id'];
			}
		}
		
		$total_res = $this->db->query("SELECT id, comment, result_type_id, issue_comment, test_env_id, defect_ids, finish_time FROM cycle_detail WHERE id in (".implode(',', $element).")");
		$total = array('comment'=>'', 'issue_comment'=>'', 'defect_ids'=>'');
		while($row = $total_res->fetch()){
			if(!empty($row['defect_ids']))
				$total['defect_ids'] = $row['defect_ids'].", ".$total['defect_ids'];
			$total['result_type_id'][] = $row['result_type_id'];
			$total['finish_time']= $row['finish_time'];
			
			// if(!empty($row['comment']))
				// $total['comment'] = $row['comment']."\n".$total['comment'];
			// if(!empty($row['issue_comment']))
				// $total['issue_comment'] = $row['issue_comment']."\n".$total['issue_comment'];
		}
			
		$res = $this->db->query("SELECT id, name FROM result_type");
		$result_type[0] = 'Blank';
		while($info = $res->fetch())
			$result_type[$info['id']] = $info['name'];
		unset($result_type[1]);
		$total['result_type_id'] = array_unique($total['result_type_id']);
		$results = $total['result_type_id'];
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
		$datas = array('datas'=>$total, 'subData'=>$data);
		
		return (json_encode($datas));
		
	}
}

?>