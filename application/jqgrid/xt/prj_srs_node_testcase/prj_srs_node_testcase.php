<?php
require_once('table_desc.php');

class xt_prj_srs_node_testcase extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id'=>array('hidden'=>true), 
			'prj_id', 
			'srs_node_id', 
			'testcase_id',
			'result_type_id'
		);
	}
	
    public function getMoreInfoForRow($row){
		$res = $this->db->query("SELECT * from testcase_last_result where prj_id={$row['prj_id']} and testcase_id={$row['testcase_id']} and tested=(SELECT MAX(tested) from testcase_last_result WHERE prj_id={$row['prj_id']} and testcase_id={$row['testcase_id']})");
		if ($prj = $res->fetch())
			$row['result_type_id'] = $prj['result_type_id'];
		return $row;
	}


}
?>