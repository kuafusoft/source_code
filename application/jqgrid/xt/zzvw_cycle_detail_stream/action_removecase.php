<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_removecase.php');

class xt_zzvw_cycle_detail_stream_action_removecase extends xt_zzvw_cycle_detail_action_removecase{

	protected function caclIDs($params){
		//$params['id'] = json_decode($params['id']);//检查$params是否非空
		$params['c_f'] = array_unique(json_decode($params['c_f']));
		if(count($params['c_f']) == 1){
			if($params['c_f'][0] == '1'){
				foreach($params['id'] as $k=>$v){
					if(!empty($v)){
							//是虚行，找到codec_stream_id， 找到对应id
						$res = $this->tool->query("SELECT cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v);
						if($info = $res->fetch()){
							if(!empty($info['codec_stream_id'])){
								$sql = "SELECT id, cycle_id, result_type_id FROM ".$this->get('table')." WHERE cycle_id=".$info['cycle_id'].
									" AND codec_stream_id=".$info['codec_stream_id'].
									" AND test_env_id=".$info['test_env_id'];
								// $params = $this->tool->parseParams();
								// foreach($params as $k=>$v){
									// if($k != "element" && $k != "c_f" && $k != "flag" && $k != "codec_stream_name"){
										// if(!empty($v)){
											// $str = " AND ".$k."=".$v;
											// $sql .= $str;
										// }
									// }
								// }
								$detail_res = $this->tool->query($sql);//" AND test_env_id=".$info['test_env_id']);
								while($detail = $detail_res->fetch()){
									if(empty($data['cycle']) && !empty($detail['cycle_id'])){
										$cycle_res = $this->tool->query("SELECT id, creater_id, assistant_owner_id FROM cycle WHERE id=".$detail['cycle_id']);
										$data['cycle'] = $cycle_res->fetch();
									}
									if(empty($detail['result_type_id'])){
											$data['no_result'][] = $detail['id'];
									}
									else{
										$data['has_result'][] = $detail['id'];
									}
								}
							}
						}
					}
				}
			}
			else{
				$res = $this->tool->query("SELECT * FROM cycle_detail WHERE id in (".implode(",", $params['id']).")");
				while($detail = $res->fetch()){
					if(empty($data['cycle']) && !empty($detail['cycle_id'])){
						$cycle_res = $this->tool->query("SELECT id, creater_id FROM cycle WHERE id=".$detail['cycle_id']);
						$data['cycle'] = $cycle_res->fetch();
					}
					if(!empty($detail['result_type_id']) && $detail['result_type_id']){
							$data['has_result'][] = $detail['id'];
					}
					else{
						$data['no_result'][] = $detail['id'];
					}
				}
			}
		}
		else
			$data = 'error';
		return $data;
	}
}

?>