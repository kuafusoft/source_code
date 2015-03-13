<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_removecase extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$info = $this->caclIDs($params);
		$cycle = '';
		$has_result = '';
		$no_result = '';
		if(!empty($info['cycle']))
			$cycle = $info['cycle'];
		if(!empty($info['has_result']))
			$has_result = $info['has_result'];
		if(!empty($info['no_result']))
			$no_result = $info['no_result'];
		
		//cycle的owner admin才可以删除case
		// $isAdmin = false;
		// if(in_array('assistant_admin', $this->userInfo->roles) || in_array('admin', $this->userInfo->roles))
			// $isAdmin = true;
		// $isAdmin = $this->userAdmin->isAdmin($this->userInfo->id);
		// if(($cycle['creater_id'] && $this->userInfo->id == $cycle['creater_id']) || $isAdmin || ($cycle['assistant_owner_id'] && $this->userInfo->id == $cycle['assistant_owner_id'])){ 
			if(!$params['flag']){
				if($no_result){
					//删除detail_step, 用到cycle_detail_id，删除与cycle_detail_id相关的所有detail_step
					// $this->tool->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $no_result).")");
					$this->tool->delete('cycle_detail', "id in (".implode(',', $no_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以去掉的
					$this->tool->delete("testcase_last_result", "cycle_detail_id in (".implode(',', $no_result).")");
				}
				if($has_result){
					$res = $this->tool->query("SELECT GROUP_CONCAT( DISTINCT d_code) AS d_code FROM ".$this->get('table')." WHERE id in (".implode(",", $has_result).")");
					if($row = $res->fetch()){
						$code = $row['d_code'];
					}
					return json_encode($code);
				}
			}
			else{
				//删detail_step
				// $this->tool->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $has_result).")");
				//删除有结果的
				$this->tool->delete('cycle_detail', "id in (".implode(',', $has_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以不加的
				$this->tool->delete("testcase_last_result", "cycle_detail_id in (".implode(',', $has_result).")");
			}
			$this->processCycle($cycle['id']);
		// }
	}
	
	protected function caclIDs($params){
		//$params['id'] = json_decode($params['id']);//检查$params是否非空
		$res = $this->tool->query("SELECT * FROM cycle_detail WHERE id in (".implode(",", $params['id']).")");
		while($detail = $res->fetch()){
			if(empty($data['cycle']) && !empty($detail['cycle_id'])){
				$cycle_res = $this->tool->query("SELECT id, creater_id, assistant_owner_id FROM cycle WHERE id=".$detail['cycle_id']);
				$data['cycle'] = $cycle_res->fetch();
			}
			if(!empty($detail['result_type_id']) && $detail['result_type_id']){
					$data['has_result'][] = $detail['id'];
			}
			else{
				$data['no_result'][] = $detail['id'];
			}
		}
		if(empty($data))
			$data = "error";
		return $data;
	}

	private function processCycle($cycle_id){
		$res = $this->tool->query("select group_concat(distinct detail.prj_id) as prj_ids, group_concat(distinct detail.compiler_id) as compiler_ids, 
			group_concat(distinct detail.build_target_id) build_target_ids, group_concat(distinct tc.testcase_type_id) as testcase_type_ids
			from cycle_detail detail left join testcase tc on tc.id = detail.testcase_id where detail.cycle_id = $cycle_id");
		if($row = $res->fetch()){
			$this->tool->update("cycle", array('prj_ids'=>$row['prj_ids'], 'compiler_ids'=>$row['compiler_ids'], 'build_target_ids'=>$row['build_target_ids'],
				'testcase_type_ids'=>$row['testcase_type_ids']), 'id='.$cycle_id
			);
		}
	}
	
}

?>