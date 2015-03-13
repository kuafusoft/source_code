<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_prj/exporter_last_result.php');
/*
应包含一些Sheets：
1. project-compiler-module情况
2. module-project-compiler情况
3. detail：testcase-project-compile情况
4. 各个project的情况

测试结果只显示Total、Pass、Fail and Others
如果选择了多个Release，则每个Release的情况并列
*/
class xt_rel_exporter_last_result extends xt_zzvw_prj_exporter_last_result{
	protected function init($params = array()){
		parent::init($params);
		$prj_ids = array();
		$this->db = dbFactory::get($this->params['db']);
		$res = $this->db->query("SELECT distinct prj_id FROM testcase_last_result WHERE rel_id IN (".implode(',', $this->params['id']).")");
		while($row = $res->fetch()){
			$prj_ids[] = $row['prj_id'];
		}
		$this->params['rel_ids'] = $this->params['id'];
		$this->params['id'] = $prj_ids;
//print_r($this->params);		
	}
};

?>
 