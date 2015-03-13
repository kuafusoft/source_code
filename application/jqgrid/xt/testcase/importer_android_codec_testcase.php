<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/importer_testcase.php');

class xt_testcase_importer_android_codec_testcase extends xt_testcase_importer_testcase{
	protected function processCase($case){
print_r($case);	
		if (!empty($case['owner'])){
			$table = $this->userAdmin->getUserTable();
			$res = $this->db->query("SELECT id FROM $table WHERE nickname=:nick", array('nick'=>$case['owner']));
			$row = $res->fetch();
			$case['owner_id'] = $row['id'];
		}
		else
			$case['owner_id'] = $this->params['owner_id'];
		
		$case['updater_id'] = $this->userInfo->id;
		
		$transfer_fields = array('testcase_type'=>'CODEC', 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 
			'auto_level'=>'MANUAL', 'testcase_priority'=>'P3');
		$fields_value = $this->tool->extractItems($transfer_fields, $case);
		if (empty($fields_value['testcase_type']))
			$case['testcase_type_id'] = $this->params['testcase_type_id'];
			
		foreach($fields_value as $field=>$value){
			if (empty($case[$field.'_id'])){
				$valuePair = array('name'=>$value);
				if ($field == 'testcase_testpoint'){
					$valuePair['testcase_module_id'] = $case['testcase_module_id'];
				}
				else if ($field == 'testcase_module'){
					$valuePair['testcase_type_ids'] = $case['testcase_type_id'];
				}
				$case[$field.'_id'] = $this->tool->getElementId($field, $valuePair, array('name'), $isNew, $this->params['db']);
				unset($case[$field]);
			}
		}
// print_r($case);		
		$case_fields = array('testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 'code', 'summary'=>"summary for ".$case['code'], 'testcase_category_id', 'testcase_source_id', 'come_from');
		$case_value = $this->tool->extractItems($case_fields, $case);
		$newCase = false;
		$case_id = $this->tool->getElementId('testcase', $case_value, array('code'), $newCase, $this->params['db']);
		if (!$newCase){
			print_r($case_value['code']." already existed \n<BR />");
		}
		if (!empty($case['tag']))
			$this->tag['element_id'][] = $case_id;
		
		$ver_fields = array('ver'=>1, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'testcase_priority_id'=>3, 'auto_run_minutes'=>0, 'manual_run_minutes'=>0, 'command'=>' ', 
			'objective'=>'(empty)', 'precondition'=>'(empty)', 'steps'=>'(empty)', 'expected_result'=>'(empty)', 'resource_link'=>'(empty)', 'parse_rule_id'=>1, 'parse_rule_content'=>' ', 
			'owner_id', 'updater_id');
		$ver_values = $this->tool->extractItems($ver_fields, $case);
		$ver_values['testcase_id'] = $case_id;
		$ver_values['edit_status_id'] = EDIT_STATUS_PUBLISHED;
		$newVer = false;
		$version_id = $this->tool->getElementId('testcase_ver', $ver_values, array(), $newVer, $this->params['db']);
		if ($newVer && !$newCase){
			$res = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->db->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		}
		if ($newVer){
			$res = $this->db->query("SELECT id as testcase_ver_id, testcase_id, owner_id, testcase_priority_id, edit_status_id, auto_level_id FROM testcase_ver WHERE id=$version_id");
			$ver = $res->fetch();
			
			$prj_ids = array();
			if (!isset($case['prj_ids'])){
				if (isset($case['platform']) && isset($case['os'])){
					$platforms = explode(';', $case['platform']);
					$os = $case['os'];
					foreach($platforms as $platform){
						if (empty($platform))
							continue;
						//需要规范platform和board_type，连接符用-
						$prj_id = $this->getProject($platform, $os, $isNew);
						$prj_ids[$prj_id] = $prj_id;
					}
				}
				else{
					if(isset($this->params['prj_ids']) && $this->params['prj_ids'])
						$prj_ids = $this->params['prj_ids'];
			}
			else{
				$prj_ids = $case['prj_ids'];
			}
print_r('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');		
print_r($prj_ids);
print_r('yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy');				
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
				$this->db->insert('prj_testcase_ver_history', $history);
			}
		}
	}
}
?>
