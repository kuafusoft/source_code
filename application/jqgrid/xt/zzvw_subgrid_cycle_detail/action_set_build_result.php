<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_set_build_result.php');

class xt_zzvw_subgrid_cycle_detail_action_set_build_result extends xt_zzvw_cycle_detail_action_set_build_result{

   public function handlePost(){
		$params = $this->parseParams();
		$params['id'] = json_decode($params['id']);
		$params['c_f'] = json_decode($params['c_f']);
// print_r($params['element']);
// print_r($params['c_f']);
		$element = '';
		$element = $this->caclIDs();
		$res = $this->db->query("SELECT id FROM cycle_detail WHERE id in (".implode(',', $element).")");
		while($row = $res->fetch())
			$this->db->update('cycle_detail', array('build_result_id'=>$params['build_result_id']), 'id='.$row['id']);
		if(count($params['id']) == 1){
			$res = $this->db->query("SELECT id, build_result_id FROM cycle_detail WHERE id=".$params['id'][0]);
			$data = $res->fetch();
			return json_encode($data);
		}
	}
}

?>