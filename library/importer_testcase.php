<?php
require_once('importer_excel.php');

class importer_testcase extends importer_excel{
	protected $testcase_type = 'LINUX BSP';
	protected function processSheetData($title, $sheet_data){
		foreach($sheet_data as $case){
			if(empty($case['testcase_module']))
				$case['testcase_module'] = $title;
			if(empty($case['testcase_testpoint']))
				$case['testcase_testpoint'] = 'Default Testpoint For '.$case['testcase_module'];
			$this->processCase($case);
		}
	}

	protected function processCase($case){
		$transfer_fields = array('testcase_type'=>$this->testcase_type, 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 'auto_level'=>'MANUAL', 'testcase_priority'=>'P3');
		$fields_value = $this->tool->extractItems($transfer_fields, $case);
		foreach($fields_value as $field=>$value){
			if (empty($case[$field.'_id'])){
				$case[$field.'_id'] = $this->getElementId($field, array('name'=>$value), array('name'));
				unset($case[$field]);
			}
		}
		$case_fields = array('testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 'code', 'summary'=>'', 'testcase_category_id', 'testcase_source_id');
		$case_value = $this->tool->extractItems($case_fields, $case);
		$newCase = false;
		$case_id = $this->getElementId('testcase', $case_value, array('code'), $newCase);
		
		$ver_fields = array('ver'=>1, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'testcase_priority_id'=>3, 'auto_run_minutes'=>0, 'manual_run_minutes'=>0, 'command'=>' ', 
			'objective'=>' ', 'precondition'=>' ', 'steps'=>' ', 'expected_result'=>' ', 'resource_link'=>' ', 'parse_rule_id'=>1, 'parse_rule_content'=>' ');
		$ver_values = $this->tool->extractItems($ver_fields, $case);
		$ver_values['testcase_id'] = $case_id;
		$ver_values['edit_status_id'] = EDIT_STATUS_PUBLISHED;
		$newVer = false;
		$version_id = $this->getElementId('testcase_ver', $version, array(), $newVer);
		if ($newVer && !$newCase){
			$res = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->db->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		}
		if ($newVer){
			$res = $this->db->query("SELECT id as testcase_ver_id, testcase_id, owner_id, testcase_priority_id, edit_status_id, auto_level_id FROM testcase_ver WHERE id=$version_id");
			$ver = $res->fetch();
			
			$prj_ids = isset($case['prj_ids']) ? $case['prj_ids'] : $this->params['prj_ids'];
			foreach($prj_ids as $prj_id){
				$link = $ver;
				$link['prj_id'] = $prj_id;
				$history = array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'act'=>'remove');
				$res = $this->db->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id AND testcase_id={$ver['testcase_id']} AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
				while($row = $res->fetch()){
					$this->db->delete('prj_testcase_ver', "prj_id=$prj_id AND testcase_id={$ver['testcase_id']}");
					$history['testcase_ver_id'] = $row['testcase_ver_id'];
					$this->db->insert('prj_testcase_ver_history', $history);
				}
				$history['testcase_ver_id'] = $ver['testcase_ver_id'];
				$history['act'] = 'add';
				$this->db->insert('prj_testcase_ver', $link);
				$this->db->insert('prj_testcase_ver', $history);
			}
		}
	}
};
?>