<?php
require_once(APPLICATION_PATH.'/jqgrid/action_cloneit.php');

class xt_zzvw_cycle_action_cloneit extends action_cloneit{

	protected function handlePost(){
		$valuePairs = $this->parseParams();
		$db = $this->get('db');
		$table = $this->get('real_table');
		$vs = $this->tool->extractData($valuePairs, $table, $db);

		$orig_id = $vs['id'];
		unset($vs['id']);
		$vs['cycle_status_id'] = CYCLE_STATUS_ONGOING;
		$vs['isactive'] = ISACTIVE_ACTIVE;	
		if($vs['creater_id'] != $this->userInfo->id){
			if(array_search($vs['creater_id'], $vs['tester_ids']) !== false){
				$k = array_search($vs['creater_id'], $vs['tester_ids']);
				unset($vs['tester_ids'][$k]);
				$vs['tester_ids'][] = $this->userInfo->id;
			}
		}		
		$vs['creater_id'] = $this->userInfo->id;
		$vs['cloned_id'] = $orig_id;	
		
	
		// save
		$affectedID = $this->save($db, $table, $vs);
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
			$s_res = $this->tool->query("select distinct codec_stream_id from cycle_detail where cycle_id=".$orig_id);
		$from .= " LEFT JOIN testcase_ver on testcase_ver.id = cycle_detail.testcase_ver_id";
		while($s_info = $s_res->fetch()){
			if($s_info['codec_stream_id'] != 0){
				$from .= " LEFT JOIN codec_stream on codec_stream.id = cycle_detail.codec_stream_id";
				break;
			}
		}
		$s_where = "";
		foreach($searchCondition as $k=>$v){
			if($v != ''){
				if(empty($s_where))
					$s_where = " AND (".$v;
				else
					$s_where .= " OR ".$v;
			}
		}
		if(!empty($s_where)){
			$s_where .= ")";
			$where .= $s_where;
		}
		$sql .= $from;
		$sql .= $where;
		
// print_r($sql);
			$this->tool->query($sql);
		$this->tool->log('save', $valuePairs);
		$this->updateTestCaseVer($affectedID, $vs['prj_id']);		
		$errorCode['code'] = ERROR_OK;
		$errorCode['msg'] = $affectedID;
		return $errorCode;
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
					$searchCondition[$v] = "testcase_ver.testcase_priority_id = 1";
					break;
				case "p2":
					$searchCondition[$v] = "testcase_ver.testcase_priority_id = 2";
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
}
?>