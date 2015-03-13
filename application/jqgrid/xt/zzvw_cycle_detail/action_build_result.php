<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_build_result extends action_jqgrid{

	public function handlePost(){
		$params = $this->parseParams();
		$res = $this->db->query("SELECT id, name FROM result_type");
		$build_result[0] = '';
		while($info = $res->fetch()){
			$build_result[$info['id']] = $info['name'];
		}
		$cols = array(
			array('name'=>'build_result_id', 'label'=>'Build Result',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'deval'=>$params['build_result_id'], 'editoptions'=>array('value'=>$build_result)),
			);
		$this->renderView('newElement.phtml', array('cols'=>$cols), '/jqgrid');	                              
	}
	
}

?>