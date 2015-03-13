<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_detail_action_getTester extends action_jqgrid{
	public function handleGet(){
		$params = $this->parseParams();
		$where = "1";
		if($params['condition']){
			$where = "id=".$params['condition'];
		}
		$sql = "SELECT tester_ids FROM cycle WHERE ".$where;
		$res = $this->db->query($sql);
		$cycle = $res->fetch();
		$tester = explode(",", $cycle['tester_ids']);
		foreach($tester as $key=>$val){
			if(empty($val)){
				unset($tester[$key]);
			}
		}
		$cycle['tester_ids'] = implode(",", $tester);
		$sql = "SELECT id, nickname as name FROM users WHERE id in (".$cycle['tester_ids'].")";
		$res = $this->userAdmin->db->query($sql);
		$this->renderView('select_item.phtml', array('type'=>"tester", 'items'=>$res->fetchAll()), '/jqgrid');
	}
}
?>