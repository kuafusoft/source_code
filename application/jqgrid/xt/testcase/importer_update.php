<?php
require_once('importer_excel.php');

class xt_testcase_importer_update extends importer_excel{

	protected function processSheetData($title, $sheet_data){
		foreach($sheet_data as $data){
			//i.MX6Q=>12, i.MX6DL=>13,i.MX6S=>14, i.MX6SL=>15, i.MX6SX=>20
			$res = $this->db->query("select ver.*, ptv.prj_id".
					" from testcase tc left join testcase_ver ver on ver.testcase_id = tc.id".
					" left join prj_testcase_ver ptv on ver.id = ptv.testcase_ver_id".
					" left join prj on prj.id = ptv.prj_id".
					" where tc.code = '".$data['code']."' and tc.testcase_type_id = 2 and ptv.testcase_ver_id is not null and prj.os_id=1". 
					" and prj.chip_id in (12,13,14,15,20) and tc.isactive = 1");
			$ret = 0;
			$ver = array();
			while($row = $res->fetch()){
				$case = $ptv = array();
				
				if(!empty($data['expected_result']))
					$row['expected_result'] = $data['expected_result'];
				if(!empty($data['command']))
					$row['command'] = $data['command'];
				if(!empty($data['summary'])){
					$case['summary'] = $data['summary'];
					if(!$ret)
						$ret = $this->db->update("testcase", $case, "id={$row['testcase_id']}");
				}
				$ptv['prj_id'] = $row['prj_id'];
				unset($row['prj_id']);
print_r('xxx');
// print_r($row);
				$oldVer_id = $row['id'];
				if(!isset($ver[$oldVer_id])){
					unset($row['id']);
					$row['update_from'] = $row['ver'];
					$row['created'] = $row['updated'] = date('Y-m-d H:i:s');
					$row['updater_id'] = $this->params['owner_id'];
					$row['update_comment'] = "update command by shan( by importing ".$title." excel to XT3.0)";
					if(!empty($data['expected_result']))
						$row['update_comment'] = "update summary, expected_result, command by shan( by importing ".$title." excel to XT3.0)";
					$row['review_comment'] = "publish by shan(by importing  ".$title." excel to XT3.0)";
					$result = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id={$row['testcase_id']}");
					$info = $result->fetch();
					$row['ver'] = $info['max_ver'] + 1;
					$ver[$oldVer_id] = $this->getElementId("testcase_ver", $row);
				}
print_r('ret');
				if($ver[$oldVer_id] != $oldVer_id){
print_r('new ver');
					$this->db->delete("prj_testcase_ver", "prj_id={$ptv['prj_id']} and testcase_ver_id={$oldVer_id} and testcase_id={$row['testcase_id']}
						and auto_level_id={$row['auto_level_id']} and edit_status_id = {$row['edit_status_id']}");
					$this->db->insert("prj_testcase_ver", array('prj_id'=>$ptv['prj_id'], 'testcase_ver_id'=>$ver[$oldVer_id], 'testcase_id'=>$row['testcase_id'],
						'auto_level_id'=>$row['auto_level_id'], 'edit_status_id'=>$row['edit_status_id'], 'testcase_priority_id'=>$row['testcase_priority_id']));
					$this->db->insert("prj_testcase_ver_history", array('prj_id'=>$ptv['prj_id'], 'testcase_ver_id'=>$ver[$oldVer_id], 'testcase_id'=>$row['testcase_id'],
						'act'=>'add', 'created'=>date('Y-m-d h:i:s')));
				}
			}
		}
	}
}
?>
