<?php
	require_once('toolfactory.php');
	
	require_once('Zend/Db.php');

	defined('REL_CATEGORY_OFFICIAL') || define('REL_CATEGORY_OFFICIAL', 1);
	defined('REL_CATEGORY_SKYWALKER') || define('REL_CATEGORY_SKYWALKER', 2);
	defined('REL_CATEGORY_OTHERS') || define('REL_CATEGORY_OTHERS', 3);
	
	class import_data{
		private $source, $target;
		private $tables = array(); // tables = array('source'=>array('target'=>target, 'fields'=>array('field'=>array('target', 'ref'=>table))))
		private $ver = array();
		private $prjs = array();
		
		private function getDb($dsn){
			if (empty($dsn))
				$source_dsn = array();
			else if (is_string($dsn))
				$dsn = array('dbname'=>$dsn);
			if (!isset($dsn['host']))$dsn['host'] = 'localhost';
			if (!isset($dsn['username']))$dsn['username'] = 'root';
			if (!isset($dsn['password']))$dsn['password'] = 'dbadmin';
			return Zend_Db::factory('PDO_MYSQL', $dsn);
		}
		
		function __construct($source_dsn, $target_dsn){
			$this->source = $this->getDb($source_dsn);
			$this->target = $this->getDb($target_dsn);
		}
		
		function setTables($tables){
			$this->tables = $tables;
			foreach($tables as $k=>$v){
				if (is_int($k)){
					$this->tables[$v] = array('target'=>$v, 'fields'=>array());
				}
				else{
					$this->tables[$k]['target'] = isset($v['target']) ? $v['target'] : $k;
					$this->tables[$k]['fields'] = isset($v['fields']) ? $v['fields'] : array();
				}
			}
		}
		
		function import_tables($tables){ 
			if (!is_array($tables))
				$tables = explode(',', $tables);
			foreach($tables as $table){
				if (isset($this->tables[$table])){
					$this->import_table($table);
				}
				else{
					print_r(">>>>>>>>>ERROR:No this table $table<<<<<<<<<<<<<<<<\n");
				}
			}
		}
		
		function import_table($table){
			$desc = $this->tables[$table];
			$target = isset($desc['target']) ? $desc['target'] : $table;
			$fields = array();
			foreach($desc['fields'] as $k=>$v){
				if (is_int($k))
					$k = $v;
				if (is_array($v))
					$fields[$k] = $v;
				else
					$fields[$k] = array('target'=>$v);
				if(empty($fields[$k]['target']))
					$fields[$k]['target'] = $k;
			}
			$res = $this->source->query("select * from $table");
			while($row = $res->fetch()){
				$insert_data = array();
				foreach($row as $f=>$v){
					if (isset($fields[$f])){
						$insert_data[$fields[$f]['target']] = $v;
					}
				}
				try{
					$this->target->insert($target, $insert_data);
				}catch(Exception $e){
					print_r("\nImport Table ERROR++++\n");
					print_r("table = $table, ".$e->getMessage()."\n");
					print_r($insert_data);
				}
			}
			unset($res);
		}
		
		private function getId($table, $row){
			$cond = array();
			if(isset($row['id']))
				$where = "id=".$row['id'];
			else{
				foreach($row as $k=>$v){
					$cond[] = "$k=:$k";
				}
				$where = implode(' AND ', $cond);
			}
			$res = $this->target->query("SELECT * FROM $table WHERE $where", $row);
			if ($tmp = $res->fetch())
				return $tmp['id'];
			try{
				$this->target->insert($table, $row);
				return $this->target->lastInsertId();
			}catch(Exception $e){
				print_r("\nGetId ERROR++++, table = $table\n");
				print_r($row);
				print_r($e->getMessage());
				return 0;
//				die('');
			}
		}
		
		function testcase(){
			$res = $this->source->query("SELECT * FROM zzvw_testcase_ver");
			while($row = $res->fetch()){
				if (empty($row['testcase_module_id']))
					continue;
				$testcase = array('id'=>$row['tcid'], 'testcase_module_id'=>$row['testcase_module_id'], 'testcase_testpoint_id'=>$row['testcase_testpoint_id'], 
					'testcase_type_id'=>$row['typeid'], 'code'=>$row['code'], 'summary'=>$row['summary'], 'testcase_category_id'=>$row['categoryid'], 
					'testcase_source_id'=>$row['sourceid'],	'isactive'=>$row['isactive']);
				$this->getId('testcase', $testcase);

				$version = array('id'=>$row['id'], 'ver'=>substr($row['name'], 7), 'testcase_id'=>$row['tcid'], 
					'edit_status_id'=>is_null($row['statusid']) ? 3 : $row['statusid'],
					'auto_level_id'=>$row['isauto'], 'testcase_priority_id'=>$row['priorityid'], 
					'auto_run_seconds'=>is_null($row['estimatetime']) ? 0 : $row['estimatetime'],
					'manual_run_seconds'=>is_null($row['manual_run_seconds']) ? 0 : $row['manual_run_seconds'], 
					'command'=>empty($row['command']) ? '' : $row['command'], 
					'objective'=>$row['objective'], 'precondition'=>$row['environment'], 'steps'=>$row['steps'],
					'expected_result'=>$row['expectedresult'], 'resource_link'=>$row['resourcelink'], 'parse_rule_id'=>$row['ruleid'],
					'parse_rule_content'=>empty($row['rule_content']) ? '' : $row['rule_content'], 'owner_id'=>$row['origownerid'], 
					'updater_id'=>$row['creatorid'], 'created'=>empty($row['createtime']) ? date('Y-m-d H:i:s') : $row['createtime'], 
					'update_comment'=>empty($row['comment']) ? '' : $row['comment'], 
					'review_comment'=>empty($row['reviewcomment']) ? '' : $row['reviewcomment']
				);
				$this->getId('testcase_ver', $version);
				$this->ver[$row['id']] = array('owner_id'=>$row['origownerid'], 'testcase_priority_id'=>$row['priorityid'], 'edit_status_id'=>is_null($row['statusid']) ? 3 : $row['statusid']);
			}
		}
		
		function prj(){
			$res = $this->source->query("SELECT link.id, link.platformid, link.osid, link.testcaseid, link.versionid FROM tms_tc_platform_link link");
			while($row = $res->fetch()){
//print_r($row);				
				if (empty($row['platformid']) || empty($row['osid']))
					continue;
				if (empty($this->ver[$row['versionid']])){
					$ver_res = $this->source->query("SELECT origownerid as owner_id, priorityid as testcase_priority_id, statusid as edit_status_id FROM tms_tc_version WHERE id=".$row['versionid']);
					if ($ver = $ver_res->fetch())
						$this->ver[$row['versionid']] = $ver;
				}
				if (empty($this->ver[$row['versionid']]))
					continue;
//print_r($ver[$row['versionid']]);
				if (empty($this->prjs[$row['platformid']][$row['osid']]))
					$this->prjs[$row['platformid']][$row['osid']] = $this->getPrjId($row['platformid'], $row['osid']);
				$prj_testcase_ver = array('prj_id'=>$this->prjs[$row['platformid']][$row['osid']], 'testcase_id'=>$row['testcaseid'], 
					'testcase_ver_id'=>$row['versionid'], 'edit_status_id'=>$this->ver[$row['versionid']]['edit_status_id'],
					'owner_id'=>$this->ver[$row['versionid']]['owner_id'], 'testcase_priority_id'=>$this->ver[$row['versionid']]['testcase_priority_id'],
					'note'=>'Import From XiaoTian');
				$this->target->insert('prj_testcase_ver', $prj_testcase_ver);
			}
			$res = $this->source->query("SELECT link.id, link.platformid, link.osid, link.testcaseid, link.versionid FROM tms_tc_platform_history link");
			while($row = $res->fetch()){
//print_r($row);				
				if (empty($this->prjs[$row['platformid']][$row['osid']]))
					$this->prjs[$row['platformid']][$row['osid']] = $this->getPrjId($row['platformid'], $row['osid']);
				$prj_testcase_ver = array('prj_id'=>$this->prjs[$row['platformid']][$row['osid']], 'testcase_id'=>$row['testcaseid'], 
					'testcase_ver_id'=>$row['versionid'], 'act'=>'import', 'note'=>'Import From XiaoTian');
				$this->target->insert('prj_testcase_ver_history', $prj_testcase_ver);
			}
		}
		
		function cycle(){
			$res = $this->source->query("SELECT * FROM tms_tr_summary");
			while($row = $res->fetch()){
				if (empty($row['platformid'])){
					print_r($row);
					continue;
				}
				$prj_id = $this->getPrjId($row['platformid'], $row['osid']);
				$rel_category_id = $this->rel_category_id($row['source']);
				$rel_id = $this->getId('rel', array('name'=>$row['releaseno'], 'rel_category_id'=>$rel_category_id));
				$cycle = array('id'=>$row['id'], 'name'=>$row['name'], 'creater_id'=>$row['creatorid'], 'start_date'=>$row['starttime'], 'end_date'=>$row['endtime'],
					'rel_id'=>$rel_id, 'cycle_type_id'=>$row['type'], 'cycle_status_id'=>$row['statusid'], 'tester_ids'=>$row['testors'], 
					'isactive'=>$row['isactive'], 'description'=>$row['description'], 'prj_id'=>$prj_id, 'link'=>empty($row['html']) ? '': $row['html']);
				$this->getId('cycle', $cycle);
			}
			$res = $this->source->query("SELECT * FROM tms_tr_detail");
			while($row = $res->fetch()){
				$detail = array('id'=>$row['id'], 'cycle_id'=>$row['summaryid'], 'testcase_id'=>$row['testcaseid'], 'testcase_ver_id'=>$row['tc_versionid'],
					'result_type_id'=>$row['resulttypeid'], 'start_time'=>$row['finishtime'], 'duration_seconds'=>empty($row['duration']) ? 0 : $row['duration'], 
					'deadline'=>$row['deadline'], 'tester_id'=>$row['testorid'], 'defect_ids'=>empty($row['crid']) ? '' : $row['crid'], 'comment'=>empty($row['comment']) ? '':$row['comment'], 
					'task_detail_id'=>empty($row['taskdetailid'])?0:$row['taskdetailid'],	'issue_comment'=>empty($row['issuecomment']) ? '':$row['issuecomment']);
				$this->getId('cycle_detail', $detail);
			}
		}
		
		function rel_category_id($source){
			switch($source){
				case 'official':
					return REL_CATEGORY_OFFICIAL;
				case 'skywalker':
					return REL_CATEGORY_SKYWALKER;
				default:
					return REL_CATEGORY_OTHERS;
			}
		}
		
		function getPrjId($platform_id, $os_id){
			$res = $this->source->query("SELECT name from tms_pf_platform WHERE id=$platform_id");
			$row = $res->fetch();
			list($orig, $chip, $board_type) = $this->parsePlatform($row['name']);
//print_r("chip = $chip, board_type = $board_type\n");			
			$res = $this->source->query("SELECT name from tms_pub_os WHERE id=$os_id");
			$row = $res->fetch();
			$os = $row['name'];
			$chip_id = $this->getId('chip', array('name'=>$chip, 'ab'=>$chip));
			$board_type_id = $this->getId('board_type', array('name'=>$board_type, 'ab'=>$board_type));
			$this->getId('os', array('id'=>$os_id, 'name'=>$os, 'ab'=>$os));
			$prj_id = $this->getId('prj', array('name'=>$chip.'-'.$board_type.'-'.$os, 'os_id'=>$os_id, 'chip_id'=>$chip_id, 'board_type_id'=>$board_type_id));
			return $prj_id;
		}
		
		function parsePlatform($platform){
//			print_r(">>>>platform = $platform<<<\n");
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
		
		function updateType_Module(){
			$res = $this->source->query("SELECT moduleid, GROUP_CONCAT(typeid) as testcase_type_ids FROM tms_tc_type_module_link group by moduleid");
			while($row = $res->fetch()){
				$this->target->update('testcase_module', array('testcase_type_ids'=>$row['testcase_type_ids']), "id=".$row['moduleid']);
			}
		}
	}
	
	$tables = array(
		'tms_pub_os'=>array('target'=>'os', 'fields'=>array('id', 'name')),
		'tms_tc_type'=>array('target'=>'testcase_type', 'fields'=>array('id', 'name')),
		'tms_tc_category'=>array('target'=>'testcase_category', 'fields'=>array('id', 'name')),
		'tms_tc_priority'=>array('target'=>'testcase_priority', 'fields'=>array('id', 'name')),
		'tms_tc_source'=>array('target'=>'testcase_source', 'fields'=>array('id', 'name')),
		'tms_tc_rule'=>array('target'=>'parse_rule', 'fields'=>array('id', 'name', 'isactive')),
		'tms_tc_status'=>array('target'=>'edit_status', 'fields'=>array('id', 'name')),
		'tms_pf_module'=>array('target'=>'testcase_module', 'fields'=>array('id', 'name', 'description', 'isactive')),
		'tms_pf_testpoint'=>array('target'=>'testcase_testpoint', 'fields'=>array('id', 'name', 'description', 'moduleid'=>'testcase_module_id', 'isactive')),
		'tms_tr_status'=>array('target'=>'cycle_status', 'fields'=>array('id', 'name')),
		'tms_tr_resulttype'=>array('target'=>'result_type', 'fields'=>array('id', 'name', 'description')),
		'tms_tr_type'=>array('target'=>'cycle_type', 'fields'=>array('id', 'name', 'description')),
	);
	
	$umbrella = array('host'=>'10.192.225.199', 'username'=>'yy', 'password'=>'', 'dbname'=>'xiaotian');
	$xt_test = array(/*'host'=>'10.192.224.202',*/ 'username'=>'root', 'password'=>'dbadmin', 'dbname'=>'xt');
	$pf = new import_data($umbrella, $xt_test);
	$pf->updateType_Module();
/*	
	$pf->setTables($tables);
	$pf->import_tables(array_keys($tables));//'tms_tc_type,tms_tc_source,tms_tc_category,tms_tc_rule,tms_tc_priority,tms_tc_status,tms_pf_module,tms_pf_testpoint,tms_tr_status,tms_tr_resulttype,tms_tr_type');
	$pf->testcase();
	$pf->prj();
	$pf->cycle();
*/	
?>