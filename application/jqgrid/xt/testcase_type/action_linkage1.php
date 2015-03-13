<?php
require_once('action_jqgrid.php');

class xt_testcase_type_action_linkage extends action_jqgrid{
	protected function handlePost(){
		$params = $this->params;
		$params['searchConditions'] = array(array('field'=>$params['field'], 'value'=>$params['value'], 'op'=>'='));
		unset($params['value']);
		unset($params['field']);
		unset($params['cond']);
		$sqls = $this->table_desc->calcSqlComponents($params, false);
		print_r($sqls);
		$sql = $this->table_desc->getSql($sqls);
		$res = $this->db->query($sql);
		$rows = $res->fetchAll();
		return json_encode($rows);
	}
	
	
}

?>