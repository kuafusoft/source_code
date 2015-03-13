<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getPrj extends action_jqgrid{

	protected function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$where = "1";
		// if($params['value'])
			// $where .= " AND creater_id IN (".$params['value'].")";
		if($params['parent']){
			$res = $this->tool->query("select group_id from cycle where id=".$params['parent']);
			if($info = $res->fetch()){
				if(!empty($info['group_id']))
					$where .= " AND cycle.group_id = ".$info['group_id'];
			}
		}
// print_r($where);
		$res = $this->tool->query("SELECT distinct prj.id, prj.name FROM cycle".
			" left join cycle_detail detail.cycle_id = cycle.id".
			" left join prj on prj.id = detail.prj_id WHERE ".$where);
		//cycle_type---prj
		return json_encode($res->fetchAll());
	}
}
?>