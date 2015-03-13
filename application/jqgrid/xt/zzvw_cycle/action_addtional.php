<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_addtional extends action_jqgrid{
	public function handleGet(){
		$params = $this->parseParams();
		//clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
		if (!empty($params['id'])){			
			$sql ="SELECT id, name FROM result_type";
			$res = $this->tool->query($sql);
			$res_tp = array();
			$res_tp['all'] = 'All Cases';
			$res_tp['P1'] = 'P1 Cases';
			$res_tp['P2'] = 'P2 Cases';
			$res_tp['blank'] = 'Untested Cases';
			while($info = $res->fetch()){
				if($info['id'] != 7 && $info['id'] != 8 && $info['id'] != 9)
				$res_tp[$info['name']] = $info['name']. " Case";
			}	
			$cols = array(
				array('id'=>'case_choose', 'name'=>'case_choose', 'label'=>'Choonse Case', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'cols'=>10, 'editoptions'=>array('value'=>$res_tp), 'defval'=>'all'),
				//array('id'=>'testcase_priority_id', 'name'=>'testcase_priority_id', 'label'=>'TestCase Priority', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>$tc_pr), 'defval'=>'all')
			);
			// $res = $this->tool->query("select distinct codec_stream_id from cycle_detail where cycle_id = ".$params['id']);
			// while($info = $res->fetch()){
				// if($info['codec_stream_id'] != 0){
					// $cols[0]['editoptions'] = array('value'=>$res_tp);
				// }
			// }
			$this->renderView('addtional_clone.phtml', array('cols'=>$cols));
		}
	}
	
}
?>