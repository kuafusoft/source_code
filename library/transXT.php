<?php
require_once('transBase.php');

class transXT extends transBase{
	protected $prj_maps = array();
	protected $rel_maps = array();
	
	function trans_table_platform($source_table){
		//construct the map
		$res = $this->target_db->query("SELECT * FROM platform");
		while($row = $res->fetch())
			$this->maps['platform'][$row['old_id']] = array('chip_id'=>$row['chip_id'], 'board_type_id'=>$row['board_type_id']);

		// 构建chip和board_type的索引表
		$os_map = array();
		$res = $this->target_db->query("SELECT * FROM os");
		while($row = $res->fetch())
			$os_map[$row['name']] = $row['id'];
		$chip_type_map = array();
		$chip_map = array();
		$board_type_map = array();
		$res = $this->target_db->query("SELECT * FROM chip_type");
		while($row = $res->fetch()){
			$chip_type_map[$row['name']] = $row["id"];
		}
		$res = $this->target_db->query("SELECT * FROM chip");
		while($row = $res->fetch()){
			$chip_map[$row['name']] = $row["id"];
		}
		$res = $this->target_db->query("SELECT * FROM board_type");
		while($row = $res->fetch()){
			$board_type_map[$row['name']] = $row["id"];
		}
		
		$source = $this->source_db->query("SELECT * FROM $source_table");
		$board_types = array();
		$i = 0;
		while($source_row = $source->fetch()){
			$newId = $this->checkNewId($source_row, 'platform');
			if (!empty($newId))
				continue;
				
			list($o, $chip, $board_type) = $this->parsePlatform($source_row['name']);
			if (empty($chip_map[$chip])){
				$chip_type_id = $this->getChipTypeId($chip, $chip_type_map, $board_type_map, $os_map);
				$chip_map[$chip] = $this->insertRow(array('name'=>$chip, 'chip_type_id'=>$chip_type_id), 'chip');
			}
			$chip_id = $chip_map[$chip];
			if (empty($board_type_map[$board_type])){
				$board_type_map[$board_type] = $this->insertRow(array('name'=>$board_type), 'board_type');
			}
			$board_type_id = $board_type_map[$board_type];
			$old_id = $source_row['id'];
			$target[] = compact('chip_id', 'board_type_id', 'old_id');
			$i ++;
			if ($i == BAT_ROWS){
				$this->insertRows($target, 'platform');
				$i = 0;
				unset($target);
				$target = array();
			}
		}
		if ($i != 0)
			$this->insertRows($target, 'platform');
		
		//reconstruct the map
		$res = $this->target_db->query("SELECT * FROM platform");
		while($row = $res->fetch())
			$this->maps['platform'][$row['old_id']] = array('chip_id'=>$row['chip_id'], 'board_type_id'=>$row['board_type_id']);
		// print_r($this->maps[$target_table]);
	}
	
	function trans_row_chip($source_row){
		//要判断chip_type
		$chipTypeId = $this->getChipTypeId($source_row['name']);
		$source_row['chip_type_id'] = $chipTypeId;
// print_r($source_row);		
		return $this->trans_row_default($source_row, 'chip');
	}
	
	function trans_table_cycle($source){
		$this->prePrjMap();
		$this->preNameMap('rel');
		$this->preNameMap('cycle_category');
		return parent::trans_table_default($source, 'cycle', array());
	}
	
	//在这之前应先导入和Cycle相关的一些表，如cycle_type, cycle_category等
	//需要分析platform + os到prj的对应
	function trans_row_cycle($source_row){
		$newRow = array();
		if (empty($source_row['platformid']) || empty($source_row['osid']))
			return array();
			
		$newRow['id'] = $source_row['id'];
		$newRow['prj_id'] = $this->getPrjId($source_row['platformid'], $source_row['osid']);
		$newRow['group_id'] = $source_row['groupid'];
		$newRow['creater_id'] = $source_row['creatorid'];
		$newRow['name'] = $source_row['name'];
		$newRow['creater_id'] = $source_row['creatorid'];
		$newRow['start_date'] = $source_row['starttime'];
		$newRow['end_date'] = $source_row['endtime'];
		$newRow['tester_ids'] = $source_row['testors'];
		$newRow['description'] = $source_row['description'];
		$newRow['isactive'] = $source_row['isactive'];
		$newRow['cloned_id'] = $source_row['cloneid'];
		
		$rel = array('name'=>$source_row['releaseno'], 'os_id'=>$source_row['osid'], 'baseline_id'=>$source_row['baselineid'],
			'config_affected_id'=>$source_row['configaffected']);
		$newRel_id = $this->checkNewId($rel, 'rel');
		$relName = strtolower($rel['name']);
		if (empty($newRel_id)){
			$this->name_maps['rel'][$relName] = $this->insertRow($rel, 'rel');
		}
		$newRow['rel_id'] = $this->name_maps['rel'][$relName];

		$newRow['cycle_status_id'] = $source_row['statusid'];
		$newRow['cycle_type_id'] = $source_row['type'];
		// $newRow['compiler_id'] = $this->getNewId(array('name'=>'GCC'), 'compiler');
		// $newRow['build_target_id'] = $this->getNewId(array('name'=>'Release'), 'build_target');
		// $newRow['test_env_id'] = $this->getNewId(array('name'=>'Default'), 'test_env');
		
		$category = array('name'=>$source_row['source']);
		$cat_name = strtolower($category['name']);
		$newCategory_id = $this->checkNewId($category, 'cycle_category');
		if (empty($newCategory_id)){
			$this->name_maps['cycle_category'][$cat_name] = $this->insertRow($category, 'cycle_category');
		}
		$newRow['cycle_category_id'] = $this->name_maps['cycle_category'][$cat_name];
		
		return $newRow;
	}
	
	//应该先导入Testcase，testcase_ver, task, task_detail, prj_testcase_ver, prj_testcase_ver_history
	function trans_row_cycle_detail($vp){
		$newRow = array();
		$newRow['id'] = $vp['id'];
		$newRow['cycle_id'] = $vp['summaryid'];
		// $newRow['build_result_id'] = $this->getNewId(array('name'=>'pass'), 'result_type');
		$newRow['result_type_id'] = $vp['resulttypeid'];
		$newRow['finish_time'] = $vp['finishtime'];
		$newRow['duration_minutes'] = $vp['duration'];
		$newRow['deadline'] = $vp['deadline'];
		$newRow['tester_id'] = $vp['testorid'];
		// $newRow['test_env_id'] = $this->getNewId(array('name'=>'Default'), 'test_env');
		$newRow['defect_ids'] = $vp['crid'];
		$newRow['task_detail_id'] = $vp['taskdetailid'];

		// $newRow['comment'] = $vp['comment'];
		$newRow['issue_comment'] = $vp['issuecomment'];
		
		// 处理testcase和testcase_ver
		$newRow['testcase_id'] = $vp['testcaseid'];
		$newRow['testcase_ver_id'] = $vp['tc_versionid'];
		return $newRow;
	}
	
	//应先导入module和testpoint
	function trans_row_testcase($vp){
		$newRow = array();
		$newRow['id'] = $vp['id'];
		$newRow['code'] = $vp['testcaseid'];
		$newRow['summary'] = $vp['name'];
		$newRow['testcase_module_id'] = $vp['moduleid'];
		$newRow['testcase_testpoint_id'] = $vp['testpointid'];

		$res = $this->source_db->query("SELECT typeid, categoryid, sourceid FROM tms_tc_version WHERE tcid={$vp['id']}");
		$ver = $res->fetch();
		
		$newRow['testcase_type_id'] = $ver['typeid'];
		$newRow['testcase_category_id'] = $ver['categoryid'];
		$newRow['testcase_source_id'] = $ver['sourceid'];
		
		return $newRow;
	}
	
	function trans_row_testcase_ver($vp){
		$newRow = array('id'=>$vp['id'], 'ver'=>substr($vp['name'], 8));
		$newRow['testcase_id'] = $vp['tcid'];
		$newRow['edit_status_id'] = $vp['statusid'];
		$newRow['auto_level_id'] = $vp['isauto'];
		$newRow['testcase_priority_id'] = $vp['priorityid'];
		$newRow['auto_run_minutes'] = !empty($vp['estimatetime']) ? $vp['estimatetime'] : 0;
		$newRow['manual_run_minutes'] = !empty($vp['manual_run_seconds']) ? $vp['manual_run_seconds'] / 60 : 0;
		$newRow['command'] = $vp['command'];
		$newRow['objective'] = $vp['objective'];
		$newRow['precondition'] = $vp['environment'];
		$newRow['expected_result'] = $vp['expectedresult'];
		$newRow['resource_link'] = $vp['resourcelink'];
		$newRow['parse_rule_id'] = $vp['ruleid'];
		$newRow['parse_rule_content'] = $vp['rule_content'];
		$newRow['owner_id'] = $vp['origownerid'];
		$newRow['updater_id'] = $vp['creatorid'];

		// $newRow['steps'] = $vp['steps'];


		$newRow['created'] = $vp['createtime'];
		$newRow['updater_id'] = $vp['createtime'];
		$newRow['update_comment'] = $vp['comment'];
		$newRow['review_comment'] = $vp['reviewcomment'];
		return $newRow;
	}
	
	function prePrjMap(){
		// prepare the prj_map
		$res = $this->target_db->query("SELECT * FROM prj");
		while($row = $res->fetch()){
			$p = $this->target_db->query("SELECT * FROM platform WHERE chip_id={$row['chip_id']} and board_type_id={$row['board_type_id']}");
			$platform = $p->fetch();
			$this->prj_maps[$platform['old_id']][$row['os_id']] = $row['id'];
		}
	}
	
	function trans_table_prj_testcase_ver($source_table){
		$this->prePrjMap();
		parent::trans_table_default($source_table, 'prj_testcase_ver', array());
	}
	
	function trans_table_prj_testcase_ver_history($source_table){
		$this->prePrjMap();
		parent::trans_table_default($source_table, 'prj_testcase_ver_history', array());
	}
	
	function trans_row_prj_testcase_ver($vp){
// print_r($vp);
		if (empty($vp['platformid']) || empty($vp['osid']))
			return array();
		$newRow = array('id'=>$vp['id']);
		$newRow['prj_id'] = $this->getPrjId($vp['platformid'], $vp['osid']);
		
		$newRow['testcase_id'] = $vp['testcaseid'];
		$newRow['testcase_ver_id'] = $vp['versionid'];
		$newRow['owner_id'] = $vp['origownerid'];
		$newRow['testcase_priority_id'] = $vp['priorityid'];
		$newRow['edit_status_id'] = $vp['statusid'];
		$newRow['auto_level_id'] = $vp['isauto'];
		return $newRow;
	}
	
	function trans_row_prj_testcase_ver_history($vp){
		if (empty($vp['platformid']) || empty($vp['osid']))
			return array();
		$newRow = array();
		$newRow['prj_id'] = $this->getPrjId($vp['platformid'], $vp['osid']);
		$newRow['testcase_id'] = $vp['testcaseid'];
		$newRow['testcase_ver_id'] = $vp['versionid'];
		return $newRow;
	}
	
	function getPrjId($platformId, $osId){
		if (empty($this->prj_maps[$platformId][$osId])){
			$p_id = $this->checkNewId(array('id'=>$platformId), 'platform');
			if (empty($p_id)){
				$this->trans_table_platform('tms_pf_platform');
				$p_id = $this->checkNewId(array('id'=>$platformId), 'platform');
			}
			$os_id = $this->checkNewId(array('id'=>$osId), 'os');
			if (empty($os_id)){
				$this->trans_table('tms_pub_os', 'os', array('id', 'name', 'isactive'));
				$os_id = $this->checkNewId(array('id'=>$osId), 'os');
			}
			$chip_id = $p_id['chip_id'];
			$board_type_id = $p_id['board_type_id'];
			// get the prj_name
			$res = $this->target_db->query("SELECT * FROM chip WHERE id=$chip_id");
			$row = $res->fetch();
			$chip_name = $row['name'];

			$res = $this->target_db->query("SELECT * FROM board_type WHERE id=$board_type_id");
			$row = $res->fetch();
			$board_type_name = $row['name'];

			$res = $this->target_db->query("SELECT * FROM os WHERE id=$os_id");
			$row = $res->fetch();
			$os_name = $row['name'];
			
			$name = $chip_name.'-'.$board_type_name.'-'.$os_name;
			$prj = compact('chip_id', 'board_type_id', 'os_id', 'name');
print_r($prj);			
			$prj_id = $this->insertRow($prj, 'prj');
			// $this->maps['prj']['id'][$prj_id] = $prj;	// 可以根据prj_name得到prj_id，可以根据prj_id得到(chip_id, board_type_id, os_id, name)
			
			$this->prj_maps[$platformId][$osId] = $prj_id;	// 可以根据Platform_id和os_id得到prj_id
		}
		return $this->prj_maps[$platformId][$osId];
	}
	
	function parsePlatform($platform){
		if (preg_match('/(.*)[-_](.*)$/', $platform, $matches)){
			switch($platform){
				case 'HDMI_Dongle':
					$matches[1] = 'i.MX6DL';
					break;
				case 'RX_5W':
					$matches[1] = 'MC9RS08KB12';
					$matches[2] = 'Other';
					break;
			}
//						print_r($matches);
		}
		else{
			$board_type = '3DS';
			switch($platform){
				case 'i.MX23':
					$board_type = 'EVK';
					break;
				case 'iSTMP3780':
					$board_type = 'Armadillo';
					break;
				case 'A11':
					$board_type = 'Other';
					break;
				case 'A13':
					$board_type = 'Other';
					break;
			}
			$matches = array(0=>$platform, 1=>$platform, 2=>$board_type);
		}
//			unset($matches[0]);
//print_r($matches);			
		return $matches;
	}
	
	protected function getChipTypeId($chipName, &$chip_type_map, &$board_type_map, &$os_map){
		$chip_type_id = 0;
		
		if (empty($board_type_map['TWR'])){
			$board_type_map['TWR'] = $this->insertRow(array('name'=>'TWR'), 'board_type');
		}
		$board_type_twr = $board_type_map['TWR'];
		
		if (empty($board_type_map['AUTOEVB'])){
			$board_type_map['AUTOEVB'] = $this->insertRow(array('name'=>'AUTOEVB'), 'board_type');
		}
		$board_type_autoevb = $board_type_map['AUTOEVB'];

		if (empty($os_map['MQX'])){
			$os_map['MQX'] = $this->insertRow(array('name'=>'MQX'), 'os');
		}
		$os_mqx = $os_map['MQX'];
		if (empty($os_map['PSDK'])){
			$os_map['PSDK'] = $this->insertRow(array('name'=>'PSDK'), 'os');
		}
		$os_psdk = $os_map['PSDK'];

		$chip_type = array('name'=>'Unknown', 'os_ids'=>'', 'board_type_ids'=>'');
		if (preg_match('/^(i\.mx|mx|m[^x]|vf|px|k)(.*)/i', $chipName, $matches)){
//print_r($matches);		
			switch(strtolower($matches[1])){
				case 'i.mx':
				case 'mx':
					$chip_type = array('name'=>'MX'.$matches[2][0], 'os_ids'=>'1,2,3,4,5,6,7', 'board_type_ids'=>'1,2,3,4,5,6,7,8,9,10,11,12,13,14');
					break;
				case 'vf':
					$chip_type = array('name'=>'Vybrid', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				case 'px':
					$chip_type = array('name'=>'PowerPC', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				case 'k':
					$chip_type = array('name'=>'Kinetis', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
				default: // m?
					$chip_type = array('name'=>'ColdFire', 'os_ids'=>$os_mqx, 'board_type_ids'=>$board_type_twr.','.$board_type_autoevb);
					break;
			}
		}
		if (empty($chip_type_map[$chip_type['name']])){
			$chip_type_map[$chip_type['name']] = $this->insertRow($chip_type, 'chip_type');
		}
		return $chip_type_map[$chip_type['name']];
	}
		
}

?>