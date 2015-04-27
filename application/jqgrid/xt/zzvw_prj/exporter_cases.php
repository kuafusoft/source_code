<?php
require_once('exporter_excel.php');
/*
应包含一些Sheets：
1. project-compiler-module情况
2. module-project-compiler情况
3. detail：testcase-project-compile情况
4. 各个project的情况

测试结果只显示Total、Pass、Fail and Others
如果选择了多个Release，则每个Release的情况并列
*/
class xt_zzvw_prj_exporter_cases extends exporter_excel{
	protected $data = array();
	protected $db = null;
	protected function init($params = array()){
		parent::init($params);
		$this->params['id'] = explode(',', $params['id']);
// print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_prj [export_type] => last_result [rel_ids] => Array ( [0] => 1 [1] => 2 ) [id] => 10,9 [real_table] => prj ) 
		$this->db = dbFactory::get($this->params['db']);
	}
	
	public function setOptions($jqgrid_action){
		$this->params['sheets'] = array(
			$this->getCasesSheet($jqgrid_action),
		);
	}
	
	protected function getCasesSheet($jqgrid_action){
		// print_r($this->params);
		$prjs = array();
		$res = $this->db->query("SELECT id, name from prj WHERE id in (".implode(',', $this->params['id']).")");
		while($row = $res->fetch())
			$prjs[$row['id']] = $row['name'];
		$sheet = array('title'=>'Project Cases', 'startRow'=>2, 'startCol'=>1);
		$row0 = array(
			array('label'=>'Module', 'index'=>'module', 'width'=>200),
			array('label'=>'Testcase ID', 'index'=>'code', 'width'=>200),
			array('label'=>'Summary', 'index'=>'summary', 'width'=>400),
		);

		foreach($this->params['id'] as $prj_id){
			$row0[] = array('label'=>$prjs[$prj_id], 'index'=>'ver_'.$prj_id, 'width'=>100);
		}
		$row0[] = array('label'=>'Different', 'index'=>'is_diff', 'width'=>100);
		$sheet['header']['rows'][0] = $row0;
		$sheet['data'] = $this->getMyData();
// print_r($sheet);		
		return $sheet;
	}
	
	protected function getMyData(){
		if (empty($this->data)){
			$data = array();
			// 先获取具体的数据
			$sql = "SELECT testcase.id, testcase_module.name as module, testcase.code, testcase.summary, testcase_ver.ver, prj.name as prj, prj.id as prj_id".
				" FROM prj_testcase_ver left join testcase on prj_testcase_ver.testcase_id=testcase.id".
				" LEFT JOIN testcase_ver ON prj_testcase_ver.testcase_ver_id=testcase_ver.id".
				" LEFT JOIN testcase_module ON testcase.testcase_module_id=testcase_module.id".
				" LEFT JOIN prj ON prj_testcase_ver.prj_id=prj.id".
				" WHERE prj_testcase_ver.prj_id in (".implode(",", $this->params['id']).") AND testcase_ver.edit_status_id IN (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.") ORDER BY module ASC, code ASC";
// print_r($sql);				
			// $condition = array(
				// array('field'=>'prj_id', 'op'=>'in', 'value'=>$this->params['id']),
				// array('field'=>'edit_status_id', 'op'=>'=', 'value'=>EDIT_STATUS_PUBLISHED)
			// );
			// $last_result = tableDescFactory::get('xt', 'prj_srs_node_ver');
			// $components = $last_result->calcSqlComponents(array('db'=>'xt', 'table'=>'prj_srs_node_ver', 'searchConditions'=>$condition), false);
			// $sql = $last_result->getSql($components);
			$res = $this->db->query($sql);
			while($row = $res->fetch()){
				$data[$row['code']]['ver_'.$row['prj_id']] = $row;
			}
			$i = 0;
			$prj_count = count($this->params['id']);
			foreach($data as $code=>$code_data){
				$this->data[$i] = array('code'=>$code);
				$first = null;
				$is_diff = false;
				$count = 0;
				foreach($code_data as $ver_prj_id=>$ver_data){
					$this->data[$i][$ver_prj_id] = 'version '.$ver_data["ver"];
					$this->data[$i]['module'] = $ver_data['module'];
					$this->data[$i]['summary'] = $ver_data['summary'];
					if(is_null($first))
						$first = $ver_data['ver'];
					else{
						if($first != $ver_data['ver'])
							$is_diff = true;
					}
					$count ++;
				}
				if($count < $prj_count)
					$is_diff = true;
				$this->data[$i]['is_diff'] = $is_diff ? 'YES' : 'NO';
				$i ++;
			}
		}
// print_r($this->data);		
		return $this->data;
	}
	
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$style = parent::calcStyle($sheetIndex, $headerIndex, $content, $default);
		if ($headerIndex == 'is_diff' && $content[$headerIndex] == 'YES'){
			$style = 'warning';
		}
		return $style;
	}
};

?>
 