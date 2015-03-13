<?php
require_once('action_jqgrid.php');

class xt_testcase_action_coverSRS extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
// print_r($params);		
		$strIds = implode(',', $params['id']);
		$prjs = array();
		$sql = "SELECT prj_testcase_ver.testcase_id, prj_testcase_ver.prj_id, prj.name FROM prj_testcase_ver LEFT JOIN prj ON prj_testcase_ver.prj_id=prj.id".
			" WHERE prj_testcase_ver.testcase_id IN ($strIds) AND prj.prj_status_id=".PRJ_STATUS_ONGOING;
		$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$prjs[$row['prj_id']] = $row['name'];
//			$prjs[$row['testcase_ver_id']][$row['prj_id']] = $row['name'];
		}
		$view_params['prjs'] = $prjs;
		
		$view_params['view_file'] = "coversrs.phtml";
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase';

		return $view_params;
	}
	
	protected function handlePost(){
//print_r($this->params);	
		// $this->params['id'] = json_decode($this->params['id']);
		foreach($this->params['id'] as $testcase_id){
			foreach($this->params['prj_ids'] as $prj_id){
				foreach($this->params['srs_node_ids'] as $srs_node_id){
					$row = compact('testcase_id', 'prj_id', 'srs_node_id');
					$res = $this->tool->query("select * from prj_srs_node_testcase WHERE prj_id=:prj_id AND srs_node_id=:srs_node_id AND testcase_id=:testcase_id", $row);
					if (!$res->rowCount())
						$this->tool->insert('prj_srs_node_testcase', $row, $this->get('db'));
					
				}
			}
		}
	}
	
}

?>