<?php
require_once('kf_tool.php');
require_once('const_def.php');
require_once('Zend/Db.php');

class xt_common extends kf_tool{
	protected $db;
	public function __construct($dsn){
		$this->db = $this->getDb($dsn);
	}

	public function getDb($dsn){
		if (empty($dsn))
			$dsn = array('dbname'=>'xt');
		else if (is_string($dsn))
			$dsn = array('dbname'=>$dsn);
		if (!isset($dsn['host']))$dsn['host'] = 'localhost';
		if (!isset($dsn['username']))$dsn['username'] = 'root';
		if (!isset($dsn['password']))$dsn['password'] = 'dbadmin';
		return Zend_Db::factory('PDO_MYSQL', $dsn);
	}

	public function getElementId($table, $valuePair, $keyFields = array(), &$is_new = true){
		$where = array();
		$realVP = array();
		$res = $this->db->query("describe $table");
		while($row = $res->fetch()){
			if (isset($valuePair[$row['Field']]))
				$realVP[$row['Field']] = $valuePair[$row['Field']];
		}
		if (empty($keyFields))
			$keyFields = array_keys($realVP);
		foreach($keyFields as $k){
			$where[] = "$k=:$k";
			$whereV[$k] = $realVP[$k];
		}
		$res = $this->db->query("SELECT * FROM $table where ".implode(' AND ', $where), $whereV);
		if ($row = $res->fetch()){
			$this->db->update($table, $realVP, "id=".$row['id']);
			$is_new = false;
			return $row['id'];
		}

		
		$is_new = true;
		$this->db->insert($table, $realVP);
		$element_id = $this->db->lastInsertId();
		return $element_id;
	}

	public function generateCycleName2($cycleInfo){
		$start_date = strtotime($cycleInfo['start_date']);
		$currentYear = (int)date('y', $start_date);
		$currentWorkWeek = (int)date('W', $start_date);
		$week = sprintf("%2dWK%02d", $currentYear, $currentWorkWeek);
		if (empty($cycleInfo['myName'])){
			$cycleInfo['myName'] = date('Ymd', $start_date);
		}
print_r($cycleInfo);		
		return $cycleInfo['prj'].'-'.$cycleInfo['cycle_type'].'-'.$week.'-'.$cycleInfo['myName'];
	}
	
	public function generateCycleName($project, $cycle_type = 'Fun', $week = '', $myName = ''){
		if (empty($week)){
			$currentYear = (int)date('y');
			$currentWorkWeek = (int)date('W');
			$week = sprintf("%2dWK%02d", $currentYear, $currentWorkWeek);
		}
		if (empty($myName)){
			$myName = date('Ymd');
		}
		return $project.'-'.$cycle_type.'-'.$week.'-'.$myName;
	}
	
	public function createCycle($cycleInfo, &$cycleName){//$prj, $rel, $target = 'Release', $compiler = 'GCC', $cycle_type = 'Fun', $week = '', $myName = ''){
		if (!isset($cycleInfo['prj_id']))
			$cycleInfo['prj_id'] = $this->getElementId('prj', array('name'=>$cycleInfo['prj']));
		if (!isset($cycleInfo['rel_id']))
			$cycleInfo['rel_id'] = $this->getElementId('rel', array('name'=>$cycleInfo['rel']));
		if (!isset($cycleInfo['compiler_id'])){
			if (!isset($cycleInfo['compiler']))
				$cycleInfo['compiler'] = 'GCC';
			$cycleInfo['compiler_id'] = $this->getElementId('compiler', array('name'=>$cycleInfo['compiler']));
		}
		if (!isset($cycleInfo['cycle_type_id'])){
			if (!isset($cycleInfo['cycle_type']))
				$cycleInfo['cycle_type'] = 'Fun';
			$cycleInfo['cycle_type_id'] = $this->getElementId('cycle_type', array('name'=>$cycleInfo['cycle_type']));
		}
		if (!isset($cycleInfo['build_target_id'])){
			if (!isset($cycleInfo['build_target']))
				$cycleInfo['build_target'] = 'Release';
			$cycleInfo['build_target_id'] = $this->getElementId('build_target', array('name'=>$cycleInfo['build_target']));
		}
		if (!isset($cycleInfo['cycle_type']))
			$cycleInfo['cycle_type'] = 'Fun';
		if (empty($cycleInfo['start_date']))
			$cycleInfo['start_date'] = date('Y-m-d');
		if (!isset($cycleInfo['name'])){
//			if (!isset($cycleInfo['myName']))
//				$cycleInfo['myName'] = $cycleInfo['compiler'].'_'.$cycleInfo['build_target'].'_'.date('Ymd');
			$cycleInfo['name'] = $cycleName = $this->generateCycleName2($cycleInfo);//['prj'], $cycleInfo['cycle_type'], $cycleInfo['start_date'], $cycleInfo['myName']);
		}
//print_r($cycleInfo);		
		$new = false;
		return $this->getElementId('cycle', $cycleInfo, $new);
	}
	
	public function generateProjectName($chip, $board_type, $os){
		return $chip.'-'.$board_type.'-'.$os;
	}
	
	public function getCaseInfo($case){
		if (!isset($case['testcase_testpoint']) && isset($case['testcase_module']))
			$case['testcase_testpoint'] = 'Default testpoint for '.$case['testcase_module'];
		$testcase = array('code'=>$case['code']);
		if (isset($case['summary']))
			$testcase['summary'] = $case['summary'];
		else
			$testcase['summary'] = 'Summary for '.$case['code'];

		$transfer_fields = array('testcase_type'=>'Linux BSP', 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD');
		foreach($transfer_fields as $field=>$defaultValue){
			$hasDefaultValue = true;
			if (is_int($field)){
				$field = $defaultValue;
				$hasDefaultValue = false;
			}
			if(isset($case[$field.'_id']))
				$testcase[$field.'_id'] = $case[$field.'_id'];
			else{
				if(!isset($case[$field]) && $hasDefaultValue)
					$case[$field] = $defaultValue;
				if (isset($case[$field])){
					$v = array('name'=>$case[$field]);
					if ($field == 'testcase_module')
						$v['testcase_type_ids'] = $testcase['testcase_type_id'];
					elseif ($field == 'testcase_testpoint')
						$v['testcase_module_id'] = $testcase['testcase_module_id'];
					$testcase[$field.'_id'] = $this->getElementId($field, $v);
				}
			}
		}
		$newCase = false;
		$case_id = $this->getElementId('testcase', $testcase, array(), $newCase);
		
		$version = array('testcase_id'=>$case_id, 'edit_status_id'=>EDIT_STATUS_PUBLISHED);
		$fields = array('testcase_priority_id'=>3, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'command'=>'', 'objective'=>'', 'precondition'=>'', 
			'steps'=>'', 'expected_result'=>'', 
			'parse_rule_id'=>1, 'parse_rule_content'=>'', 'auto_run_seconds'=>0, 'manual_run_seconds'=>0,
			//'created'=>date('Y-m-d H:i:s'), 'update_comment'=>'', 'review_comment'=>''
			);
		foreach($fields as $field=>$defaultValue){
			$version[$field] = isset($case[$field]) ? $case[$field] : $defaultValue;
		}
		$newVer = false;
		$version_id = $this->getElementId('testcase_ver', $version, array(), $newVer);
		if ($newVer && !$newCase){
			$res = $this->db->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			$row = $res->fetch();
			$max_ver = $row['max_ver'];
			$this->db->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		}
		return array('testcase_id'=>$case_id, 'testcase_ver_id'=>$version_id);
	}	
	
	protected function prj_testcase_ver($prj_id, $testcase_ver){
		$v = array('prj_id'=>$prj_id, 'testcase_id'=>$testcase_ver['testcase_id'], 'testcase_ver_id'=>$testcase_ver['id'], 
			'owner_id'=>$testcase_ver['owner_id'], 'testcase_priority_id'=>$testcase_ver['testcase_priority_id'],
			'edit_status_id'=>$testcase_ver['edit_status_id'], 'auto_level_id'=>$testcase_ver['auto_level_id']);
		$new = false;
		$link_id = $this->getElementId('prj_testcase_ver', $v, array(), $new);
//print_r("link_id = $link_id, new = $new, testcase_ver = ");		
//print_r($testcase_ver);	
		if ($new == true){
			$v = array('prj_id'=>$prj_id, 'testcase_id'=>$testcase_ver['testcase_id'], 'testcase_ver_id'=>$testcase_ver['id'], 'act'=>'add');
			$this->db->insert('prj_testcase_ver_history', $v);
		}
	}
	
	public function getTestcaseInfo($testcase, $prj_ids){
		$caseInfo = $this->getCaseInfo($testcase);
		$res = $this->db->query("SELECT * FROM testcase_ver WHERE id=".$caseInfo['testcase_ver_id']);
		$ver = $res->fetch();
		foreach($prj_ids as $prj_id){
			$this->prj_testcase_ver($prj_id, $ver);
		}
	}
	
	protected function getResultId($result){
		$result = strtolower($result);
			
		switch(strtolower($result)){
			case 'ok':
			case 'pass':
				$result = 'pass';
				break;
			case 'fail':
			case 'nok':
				$result = 'fail';
				break;
			case 'na':
			case 'n/a':
				$result = 'N/A';
				break;
			case 'nt':
			case 'n/t':
				$result = 'N/T';
				break;
			case 'ns':
			case 'n/s':
				$result = 'N/S';
				break;
		}
		if (stripos($result, 'not support') !== false)
			$result = 'N/S';
		
		return $this->getElementId('result_type', array('name'=>$result));
	}
	
	protected function getChipTypeId($chipName){
		$chip_type_id = 0;
		$os_mqx = $this->getElementId('os', array('name'=>'mqx'));
		$board_type_twr = $this->getElementId('board_type', array('name'=>'twr'));
		$board_type_autoevb = $this->getElementId('board_type', array('name'=>'autoevb'));
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
			$chip_type_id = $this->getElementId('chip_type', $chip_type);
		}
//print_r("chipname = $chipName, type_id = $chip_type_id\n");		
		return $chip_type_id;
	}
	
	public function getChipId($chipName){
		$chip_type_id = $this->getChipTypeId($chipName);
		return $this->getElementId('chip', array('name'=>$chipName, 'chip_type_id'=>$chip_type_id));
	}
	
	function excelTime($date, $time = false) {
		if (function_exists('GregorianToJD')) {
			if (is_numeric($date)) {
				$jd = GregorianToJD(1, 1, 1970);
				$gregorian = JDToGregorian($jd + intval($date) - 25569);
				$date = explode('/', $gregorian);
				$date_str = str_pad($date[2], 4, '0', STR_PAD_LEFT) . "-" . str_pad($date[0], 2, '0', STR_PAD_LEFT) . "-" . str_pad($date[1], 2, '0', STR_PAD_LEFT) . ($time ? " 00:00:00" : '');
				return $date_str;
			}
		} else {
				$date = $date > 25568 ? $date + 1 : 25569;
				/*There was a bug if Converting date before 1-1-1970 (tstamp 0)*/
				$ofs = (70 * 365 + 17 + 2) * 86400;
				$date = date("Y-m-d", ($date * 86400) - $ofs) . ($time ? " 00:00:00" : '');
		}
		return $date;
	}
	
	
};

?>