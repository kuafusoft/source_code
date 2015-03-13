<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');

class xt_zzvw_cycle_action_save extends action_save{
	
	protected function save($db, $table, $pair){
		if($pair['cloneit'] == 'true')
			return $this->cloneit($db, $table, $pair);
		$pair = $this->prepare($db, $table, $pair);
// print_r($pair);
		return $this->_saveOne($db, $table, $pair);
	}
	
	protected function cloneit($db, $table, $valuePairs){
// print_r($db);
		$params = $this->parseParams();
		$orig_id = $params['id'];
		$vs = $this->tool->extractData($valuePairs, $table, $db);
		$vs['cycle_status_id'] = CYCLE_STATUS_ONGOING;
		$vs['isactive'] = ISACTIVE_ACTIVE;	
		if($vs['creater_id'] != $this->userInfo->id){
			if(!empty($vs['tester_ids'])){
				if(array_search($vs['creater_id'], $vs['tester_ids']) !== false){
					$k = array_search($vs['creater_id'], $vs['tester_ids']);
					unset($vs['tester_ids'][$k]);
					$vs['tester_ids'][] = $this->userInfo->id;
				}
			}
		}		
		$vs['creater_id'] = $this->userInfo->id;
		$vs['cloned_id'] = $orig_id;			
	
		// save
		$affectedID = $this->_saveOne($db, $table, $vs);
		// save the cycle_detail info
		$searchCondition = $this->getSearchCondition($valuePairs, $orig_id );
		$p_sql = "SELECT prj_id FROM cycle WHERE id = $orig_id";
		$res = $this->tool->query($p_sql);
		$cycle = $res->fetch();
		$testcase = array();
		$is_same_prj = true;
		if($vs['prj_id'] != $cycle['prj_id']){
			$is_same_prj = false;
			$c_sql = "SELECT DISTINCT testcase_id FROM cycle_detail WHERE cycle_id =".$orig_id;
			$res = $this->tool->query($c_sql);
			while($info = $res->fetch()){
				if(!empty($info['testcase_id']))
					$caselist[] = $info['testcase_id'];
			}
			if(!empty($caselist)){
				$t_sql = "SELECT DISTINCT testcase_id FROM prj_testcase_ver".
					" WHERE prj_id=".$vs['prj_id'].
					" AND testcase_id IN (".implode(", ", $caselist).")".
					" AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
				$res = $this->tool->query($t_sql);
				while($info = $res->fetch()){
					if(!empty($info['testcase_id']))
						$testcase[] = $info['testcase_id'];
				}
			}
		}
		$sql = "INSERT INTO cycle_detail (cycle_id, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, build_result_id, tester_id)".
				" SELECT $affectedID, cycle_detail.testcase_ver_id, cycle_detail.testcase_id, cycle_detail.codec_stream_id, cycle_detail.test_env_id, 1, tester_id";
		$from = " FROM cycle_detail";
		$where = " WHERE cycle_detail.cycle_id=".$orig_id;
		if(!$is_same_prj){
			if(!empty($testcase)){
// print_r($testcase);
				$where .= " AND cycle_detail.testcase_id in (".implode(", ", $testcase).")";
			}
		}
		$from .= " LEFT JOIN testcase_ver on testcase_ver.id = cycle_detail.testcase_ver_id";
		// $s_res = $this->tool->query("select distinct codec_stream_id from cycle_detail where cycle_id=".$orig_id);
		// while($s_info = $s_res->fetch()){
			// if($s_info['codec_stream_id'] != 0){
		if($vs['group_id'] == 3 || $vs['group_id'] == 9 )
			$from .= " LEFT JOIN codec_stream on codec_stream.id = cycle_detail.codec_stream_id";
				// break;
			// }
		// }
		$tc_where = "";
		foreach($searchCondition as $k=>$v){
			if($k == 'priority')
				continue;
			else if($v != ''){
				if(empty($tc_where))
					$tc_where = " AND (".$v;
				else
					$tc_where .= " OR ".$v;
			}
		}
		
		if(!empty($searchCondition['priority'])){
			$tc_prioirty = $cs_prioirty = '';
			foreach($searchCondition['priority'] as $k=>$v){
// print_r($v);
				if($k == 'testcase'){
					foreach($v as $p){
						if(empty($tc_where))
							$tc_prioirty = " AND (".$p;
						else
							$tc_prioirty .= " OR ".$p;
					}
					// if(empty($tc_where))
						// $tc_prioirty .= ")";
				}
				else if($k == 'codec_stream'){
					foreach($v as $p){
						if(empty($tc_where))
							$cs_prioirty = " AND (".$p;
						else
							$cs_prioirty .= " OR ".$p;
					}
					// if(empty($tc_where))
						// $cs_prioirty .= ")";
				}
			}
		}
		if(!empty($tc_where))
			$where .= $tc_where;
		$sql .= $from;
		$sql .= $where;
		if(!empty($tc_prioirty)){
// print_r($sql.$tc_prioirty.") AND cycle_detail.codec_stream_id = 0");
			$this->tool->query($sql.$tc_prioirty.") AND cycle_detail.codec_stream_id = 0");
			if(!empty($cs_prioirty)){
// print_r($sql.$cs_prioirty.") AND cycle_detail.codec_stream_id != 0");
				$this->tool->query($sql.$cs_prioirty.") AND cycle_detail.codec_stream_id != 0");
			}
		}
		else{
			if(!empty($tc_where))
				$sql .= ")";
			$this->tool->query($sql);
		}
		
		$this->tool->log('save', $valuePairs);
		$this->updateTestCaseVer($affectedID, $vs['prj_id']);		
		return $affectedID;
	}
	
	protected function afterSave($affectedID){
		parent::afterSave($affectedID);
		$params = $this->parseParams();
		if($params['template']){
			$this->cycleTemplate($params, $affectedID);
		}
    }
	
	//only for codec & fas so far
	private function cycleTemplate($params, $affectedID){
		$cycle_sql = "select cycle.test_env_id, cycle.group_id, cycle.prj_id, os.name as os from cycle".
				" left join prj on prj.id = cycle.prj_id".
				" left join os on os.id = prj.os_id".
				" where cycle.id = ".$affectedID;
		$cycle_res = $this->tool->query($cycle_sql);
// print_r($params);
		$cycle = $cycle_res->fetch();
		//default的template是怎么用的??????
		
		//根据cycle-type做判断？？？
		if($cycle['group_id'] == 3 || $cycle['group_id'] == 9 ){
			$tag_res = $this->tool->query("select name, element_ids from tag where id = ".$params['tag']);
			$tag = $tag_res->fetch();
			$sql = "select stream.id, stream.testcase_ids as trickmode_ids, type.name as type, type.testcase_ids as testcase_ids".
				" from codec_stream stream".
				" left join codec_stream_type type on type.id = stream.codec_stream_type_id".
				" where stream.id in (".$tag['element_ids'].") and stream.isactive = 1 and stream.codec_stream_format_id != 56";//not in custom streams
			switch($params['template']){
				case 1://BAT
					$cond['stream'][0] = " and stream.testcase_priority_id in (1)";
					$cond['case'][0] = " and ver.testcase_priority_id in (1, 2, 3)";
					break;
				case 2: //FUNCTION
					$cond['stream'][0] = " and stream.testcase_priority_id in (1)";
					$cond['stream'][1]  = " and stream.testcase_priority_id in (2)";
					$cond['case'][0]  = " and ver.testcase_priority_id in (1, 2, 3)";
					$cond['case'][1]  = " and ver.testcase_priority_id in (1)";
					break;
				case 3: //FULL
					$cond['stream'][0] = " and stream.testcase_priority_id in (1)";
					$cond['stream'][1]  = " and stream.testcase_priority_id in (2)";
					$cond['stream'][2]  = " and stream.testcase_priority_id in (3)";
					$cond['case'][0]  = " and ver.testcase_priority_id in (1, 2, 3)";
					$cond['case'][1]  = " and ver.testcase_priority_id in (1, 2, 3)";
					$cond['case'][2]  = " and ver.testcase_priority_id in (1)";
					break;
			}
			foreach($cond['stream'] as $k=>$s_cond){
				$res = $this->tool->query($sql.$s_cond);
				if($cycle['group_id'] == 9){
// print_r("xxxxxxxxxxxxxxx");
					while($info = $res->fetch())
						$csinfo[$info['type']][$info['testcase_ids']][] = $info['id'];
	// print_r('start:');
					foreach($csinfo as $typeinfo){
						foreach($typeinfo as $testcase_ids=>$streaminfo){
							$c_cond = $cond['case'][$k];
							$case_sql = "select ptv.testcase_id, ptv.testcase_ver_id from prj_testcase_ver ptv".
								" left join testcase_ver ver on ver.id = ptv.testcase_ver_id".
								" left join testcase on testcase.id = ptv.testcase_id".
								" where ptv.testcase_id in (".$testcase_ids.") and ptv.prj_id = ".$cycle['prj_id'].$c_cond.
								" and testcase.testcase_type_id = ".$params['testcase_type_id'].
								" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
	// print_r(count($streaminfo)."\n");
							$case_res = $this->tool->query($case_sql);
	// print_r($streaminfo);
	// print_r($testcase_ids);
							while($case = $case_res->fetch()){
								if($case['testcase_ver_id']){
	// print_r(count($case)."\n");
									$detailinfo = array('cycle_id'=>$affectedID, 'test_env_id'=>$cycle['test_env_id'], 'testcase_id'=>$case['testcase_id'],
										'testcase_ver_id'=>$case['testcase_ver_id']);
									$streaminfo = array_unique($streaminfo);
									foreach($streaminfo as $streamid){
										$detailinfo['codec_stream_id'] = $streamid;
										$detail[] = $this->tool->getElementId('cycle_detail', $detailinfo);
									}
								}
							}
							
						}
					}
// print_r("fas + linux".count($detail)."\n");
				}
				else{
// print_r("yyyyyyyyyyyyyyyy");
					while($info = $res->fetch())
						$csinfo[$info['id']]= $info['trickmode_ids'];
	// print_r('start:');
					//trickmodes + stream
					foreach($csinfo as $streamid=>$trickmode_ids){
						$c_cond = $cond['case'][$k];
						if(!empty($trickmode_ids)){
							if(preg_match("/^,(.*)$/", $trickmode_ids, $matches))
								$trickmode_ids = $matches[1];
							$case_sql = "select ptv.testcase_id, ptv.testcase_ver_id from prj_testcase_ver ptv".
								" left join testcase_ver ver on ver.id = ptv.testcase_ver_id".
								" left join testcase on testcase.id = ptv.testcase_id".
								" where ptv.testcase_id in (".$trickmode_ids.") and ptv.prj_id = ".$cycle['prj_id'].$c_cond.
								" and testcase.testcase_type_id = ".$params['testcase_type_id'].
								" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
	// print_r($trickmode_ids);
	// print_r($c_cond);
	// print_r($cycle['prj_id']);
	// print_r($params['testcase_type_id']);
							$case_res = $this->tool->query($case_sql);
							while($case = $case_res->fetch()){
// print_r($case);
								if($case['testcase_ver_id']){
	// print_r(count($case)."\n");
									$detailinfo = array('cycle_id'=>$affectedID, 'test_env_id'=>$cycle['test_env_id'], 'codec_stream_id'=>$streamid, 'testcase_id'=>$case['testcase_id'], 'testcase_ver_id'=>$case['testcase_ver_id']);
									$detail[] = $this->tool->getElementId('cycle_detail', $detailinfo);
								}
							}
						}
					}
// print_r("android".count($detail)."\n");
				}
			}
			if($cycle['group_id'] == 3){
				$result = $this->tool->query("select os.name as name from prj left join os on prj.os_id = os.id where prj.id = {$cycle['prj_id']}");
				if($os = $result->fetch()){
					if(stripos(strtolower($os['name']), "android") !== false){
						$modulelist = array('Webgl', 'Memory Leak', 'Multi-Instance', 'Encoder');
						switch($params['template']){
							case 1://BAT
								$tccond = " and ver.testcase_priority_id in (1)";
								break;
							case 2: //FUNCTION
								$tccond  = " and ver.testcase_priority_id in (1, 2)";
								break;
							case 3: //FULL
								$tccond  = " and ver.testcase_priority_id in (1, 2, 3)";
								break;
						}
						foreach($modulelist as $module){
							$testcase_module_id = $this->tool->getElementId("testcase_module", array('name'=>$module), array('name'));
							$case_sql = "select ptv.prj_id, ptv.testcase_id, ptv.testcase_ver_id from prj_testcase_ver ptv".
								" left join testcase_ver ver on ver.id = ptv.testcase_ver_id".
								" left join testcase on testcase.id = ptv.testcase_id".
								" where ptv.prj_id in ({$cycle['prj_id']})".$tccond.
								" and testcase.testcase_module_id = {$testcase_module_id}".
								" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
	// print_r(count($streaminfo)."\n");
							$case_res = $this->tool->query($case_sql);
	// print_r($streaminfo);
	// print_r($testcase_ids);
							while($case = $case_res->fetch()){
								if($case['testcase_ver_id']){
	// print_r(count($case)."\n");
									$detailinfo = array('cycle_id'=>$affectedID, 'test_env_id'=>$cycle['test_env_id'], 'codec_stream_id'=>0, 
										'testcase_id'=>$case['testcase_id'], 'testcase_ver_id'=>$case['testcase_ver_id'], 'prj_id'=>$case['prj_id']);
									$detail[] = $this->tool->getElementId('cycle_detail', $detailinfo);
								}
							}
						}
					}	
				}
			}
		}
	}

	private function updateTestCaseVer($cycle_id, $prj_id){
		if(empty($prj_id)){
			//从cycle里面取出prj_id来
			$sql = "SELECT prj_id FROM cycle WHERE id=".$cycle_id;
			$res = $this->tool->query($sql);
			$info = $res->fetch();
			$prj_id = $info['prj_id'];
		}
		$sql = "SELECT DISTINCT testcase_id as testcase_id FROM cycle_detail WHERE cycle_id=".$cycle_id;
			$res = $this->tool->query($sql);
		while($row = $res->fetch()){
			$ver_sql = "SELECT testcase_ver_id FROM prj_testcase_ver".
				" WHERE testcase_id=".$row['testcase_id'].
				" AND prj_id=".$prj_id.
				" AND edit_status_id".
				" IN (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
				$ver_res = $this->tool->query($ver_sql);
			$ver = $ver_res->fetch();
			if($ver){
				$data = array('testcase_ver_id'=>$ver['testcase_ver_id']);
					$this->tool->update('cycle_detail', $data, "cycle_id=".$cycle_id." AND testcase_id=".$row['testcase_id']);
			}
		}
	}
	
	private function getSearchCondition($valuePairs, $cycle_id){
		$searchCondition = array();
		$res = $this->tool->query("select id, name from result_type");
		while($info = $res->fetch()){
			$info['name'] = strtolower($info['name']);
			$result_type[$info['name']] = $info['id'];
		}
// print_r($result_type);
		foreach($valuePairs['case_choose'] as $k=>$v){
			$key = $k;
			$v = strtolower($v);
			switch($v){
				case "all":
					$searchCondition[$v] = "";
					break;
				case "p1":
					$searchCondition['priority']['testcase'][$v] = "testcase_ver.testcase_priority_id = 1";//codec_stream_id == 0
					if($valuePairs['group_id'] == 3 || $valuePairs['group_id'] == 9 )
						$searchCondition['priority']['codec_stream'][$v] = " codec_stream.testcase_priority_id = 1";// codec_stream_id != 0
					break;
				case "p2":
					$searchCondition['priority']['testcase'][$v] = "testcase_ver.testcase_priority_id = 2";
					if($valuePairs['group_id'] == 3 || $valuePairs['group_id'] == 9 )
						$searchCondition['priority']['codec_stream'][$v] = " codec_stream.testcase_priority_id = 2";
					break;
				case "pass":
					$results[] = $result_type[$v];
					break;
				case "fail":
					$results[] = $result_type[$v];
					break;
				case "n/t":
					$results[] = $result_type[$v];
					break;
				case "n/s":
					$results[] = $result_type[$v];
					break;
				case "n/a":
					$results[] = $result_type[$v];
					break;
				case "skip":
					$results[] = $result_type[$v];
					break;
				case "blank":
					$results[] = 0;
					break;
			}
		}
// print_r($searchCondition);
		if(!empty($results)){
			if(is_array($results))
				$searchCondition['result_type_id'] = "cycle_detail.result_type_id in (".implode(",", $results).")";
		}
		return $searchCondition;
	}
}
?>