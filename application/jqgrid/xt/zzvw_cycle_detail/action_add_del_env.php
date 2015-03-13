<?php

require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_jqgrid.php');

class xt_zzvw_cycle_detail_action_add_del_env extends xt_zzvw_cycle_detail_action_jqgrid{

   public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$real_table = $this->get('real_table');
		if (!empty($params['test_env_id'])){
			$cycle_id = $params['parent'];
			$element = $this->caclIDs($params);
			if($element == "error")
				return "error";
			$params['test_env_id'] = json_decode($params['test_env_id']);
			if(!$params['isDel']){//删除
				$res = $this->tool->query("SELECT * FROM ".$real_table ." WHERE id in (".implode(',', $element).")");
				while($row = $res->fetch()){//if就可以了
					$this->set_feild($row, $params['test_env_id'], $cycle_id, 'test_env_id');
				}	
			}
			else{
				foreach($params['test_env_id'] as $env){
				//最好给一个警告或者选择
					$this->tool->delete($real_table , "id in (".implode(',', $element).") AND test_env_id = ".$env);
				}
				// if($params['isDel'] == 2){// add & modify
					// $this->tool->update("cycle_detail", array('test_env_id'=>$params['test_env_id'][0]), "id in (".implode(',', $element).")");
					// unset($params['test_env_id'][0]);
					// if(!empty($params['test_env_id'])){
						// $res = $this->tool->query("SELECT * FROM ".$real_table ." WHERE id in (".implode(',', $element).")");
						// while($row = $res->fetch()){//if就可以了
							// $this->set_feild($row, $params['test_env_id'], $cycle_id, 'test_env_id');
						// }
					// }
				// }
			}
		}                                    
	}
	
}

?>