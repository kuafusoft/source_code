<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_removecases extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		$params = $this->parseParams();
		$cycle = '';
		$has_result = array();
		$no_result = array();
		$params['id'] = json_decode($params['id']);//检查$params是否非空
		$params['c_f'] = json_decode($params['c_f']);
		if (!empty($params['id'])){
			foreach($params['id'] as $k=>$v){
				$this->caclRecord($v, $params['c_f'][$k], $cycle, $has_result, $no_result);
			}
		}
		//cycle的owner admin才可以删除case
		$isAdmin = $this->userAdmin->isAdmin($this->userInfo->id);
		if(($cycle['creater_id'] && $this->userInfo->id == $cycle['creater_id']) || $isAdmin){ 
			if(!$params['flag']){
				if($no_result){
					//删除detail_step, 用到cycle_detail_id，删除与cycle_detail_id相关的所有detail_step
					$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $no_result).")");
					$this->db->delete('cycle_detail', "id in (".implode(',', $no_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以去掉的
				}
				if($has_result){
					$res = $this->db->query("SELECT code FROM zzvw_cycle_detail WHERE id in (".implode(",", $has_result).")");
					while($row = $res->fetch()){
						$code[] = $row['code'];
					}
					return json_encode($code);
				}
			}
			else{
				//删detail_step
				$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $has_result).")");
				//删除有结果的
				$this->db->delete('cycle_detail', "id in (".implode(',', $has_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以不加的
			}
		}
	}
	
}

?>