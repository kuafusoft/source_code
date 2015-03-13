<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/importer_testcase.php');

class xt_testcase_importer_fas_testcase extends xt_testcase_importer_testcase{
	protected function processCase($case){
// print_r($case);	
		if(!empty($case['auto_level'])){
			if(strtolower($case['auto_level']) == 'semi-auto')
				$case['auto_level'] = 'Patial Auto';
			else if(strtolower($case['auto_level']) == 'cancel'){
				unset($case['auto_level']);
				$case['isactive'] = 2;
			}
		}
		if (!empty($case['owner'])){
			$table = $this->userAdmin->getUserTable();
			$res = $this->db->query("SELECT id FROM $table WHERE nickname=:nick", array('nick'=>$case['owner']));
			$row = $res->fetch();
			$case['owner_id'] = $row['id'];
		}
		else
			$case['owner_id'] = $this->params['owner_id'];
		
		$case['updater_id'] = $this->userInfo->id;
		
		$transfer_fields = array('testcase_type'=>'FAS', 'testcase_module', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 
			'auto_level'=>'MANUAL', 'testcase_priority'=>'P3');//'testcase_testpoint', 
		$fields_value = $this->tool->extractItems($transfer_fields, $case);
		if (empty($fields_value['testcase_type']))
			$case['testcase_type_id'] = $this->params['testcase_type_id'];
			
		foreach($fields_value as $field=>$value){
			if (empty($case[$field.'_id'])){
				$case[$field.'_id'] = 0;
				if(!empty($value) || $value != ''){
					$valuePair = array('name'=>$value);
					if ($field == 'testcase_testpoint'){
						$valuePair['testcase_module_id'] = $case['testcase_module_id'];
					}
					else if ($field == 'testcase_module'){
						$valuePair['testcase_type_ids'] = $case['testcase_type_id'];
					}
					$case[$field.'_id'] = $this->tool->getElementId($field, $valuePair, array('name'), $isNew, $this->params['db']);
				}
				unset($case[$field]);
			}
		}
// print_r($case);		
		$case_fields = array('testcase_type_id', 'testcase_module_id', 'code', 'summary'=>"summary for ".$case['code'], 'testcase_category_id', 'testcase_source_id', 'come_from');//, 'testcase_testpoint_id'
		$case_value = $this->tool->extractItems($case_fields, $case);
		$newCase = false;
		if(!empty($case['isactive']) && $case['isactive'] == 2){
			$case_value['isactive'] = 2;
			$case_id = $this->tool->getElementId('testcase', $case_value, array('code'), $newCase, $this->params['db']);
print_r("inactive: ".$case_id."\n<BR />");
			return;
		}
		$case_id = $this->tool->getElementId('testcase', $case_value, array('code'), $newCase, $this->params['db']);
		// if (!$newCase){
			// print_r($case_value['code']." already existed \n<BR />");
		// }
		if (!empty($case['tag']))
			$this->tag['element_id'][] = $case_id;
		
		$ver_fields = array('ver'=>1, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'testcase_priority_id'=>3, 'auto_run_minutes'=>0, 'manual_run_minutes'=>0, 'command'=>' ', 
			'objective'=>'(empty)', 'precondition'=>'(empty)', 'steps'=>'(empty)', 'expected_result'=>'(empty)', 'resource_link'=>'(empty)', 'parse_rule_id'=>1, 'parse_rule_content'=>' ', 
			'owner_id', 'updater_id');
		$ver_values = $this->tool->extractItems($ver_fields, $case);
		$ver_values['testcase_id'] = $case_id;
		$ver_values['edit_status_id'] = EDIT_STATUS_PUBLISHED;
		$newVer = false;
		$version_id = $this->getId('testcase_ver', $ver_values, array(), $newVer, $this->params['db']);
		if($version_id == 'error')
			return;
		if ($newVer && !$newCase){
			$res = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->db->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		}
		// if ($newVer){
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
					else{
						
						if(isset($case['Linux']) && $case['Linux'] == 'Y'){
							$prj_ids[0] = 'xxx';
							$prj = "i.MX6Q-ARD-Linux";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
							$prj = "i.MX6DL-ARD-Linux";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
						}
						if(isset($case['MQX']) && $case['MQX'] == 'Y'){
							$prj_ids[0] = 'xxx';
							$prj = "k70f120m-twr-mqx";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
						}
						
						if(isset($case['FAS']) && $case['FAS'] == 'FAS'){
							$prj_ids[0] = 'xxx';
							$prj = "i.MX6Q-ARD-Linux";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
							$prj = "i.MX6DL-ARD-Linux";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
							$prj = "k70f120m-twr-mqx";
							$prj_ids[] = $this->getElementId('prj', array("name"=>$prj), array('name'));
						}
					}
				}
			}
			else{
				$prj_ids = $case['prj_ids'];
			}
			
			if(!empty($prj_ids)){
				foreach($prj_ids as $prj_id){
					$link = $ver;
					$link['prj_id'] = $prj_id;
					$history = array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'act'=>'remove');
					$res0 = $this->db->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id".
						" AND testcase_id={$ver['testcase_id']} AND testcase_ver_id={$version_id}".
						" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
					if($row0 = $res0->fetch()){
						continue;
					}
					else{
						$res1 = $this->db->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id".
						" AND testcase_id={$ver['testcase_id']}".
						" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
						while($row = $res1->fetch()){
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
		// }
	}
	
	private function getId($table, $valuePair, $keyFields = array(), &$is_new = true){
		static $elements = array();
		$cached = false;
		if (!empty($keyFields)){
			if(in_array('code', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'code')
						$keyField = $keyFields[$k];
				}
			}
			else if(in_array('name', $keyFields)){
				$cached = true;
				foreach($keyFields as $k=>$v){
					if($v == 'name')
						$keyField = $keyFields[$k];
				}
			}
		}
		if (!$cached || empty($elements[$table][$valuePair[$keyField]])){
			$where = array();
			$realVP = array();
			$res = $this->db->query("describe $table");
			while($row = $res->fetch()){
				if (isset($valuePair[$row['Field']]))
					$realVP[$row['Field']] = $valuePair[$row['Field']];
			}
// if($table == 'testcase_ver')
// print_r($realVP);
			if (empty($keyFields))
				$keyFields = array_keys($realVP);
			foreach($keyFields as $k){
				$where[] = "$k=:$k";
				$whereV[$k] = $realVP[$k];
			}
			$res = $this->db->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
			while ($row = $res->fetch()){
				$this->db->update($table, $realVP, "id=".$row['id']);
				$is_new = false;
			}
			if(!empty($row['id']))
				return $row['id'];
			return 'error';
			// $is_new = true;
			// $this->db->insert($table, $realVP);
			// $element_id = $this->db->lastInsertId();
			// if ($cached)
				// $elements[$table][$keyField] = $element_id;
			// return $element_id;
		}
		$is_new = false;
		return $elements[$table][$valuePair[$keyField]];
	}
}
?>
