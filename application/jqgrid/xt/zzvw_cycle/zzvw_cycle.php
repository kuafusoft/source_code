<?php
require_once('table_desc.php');

class xt_zzvw_cycle extends table_desc{
	protected function init($db, $table, $params = array()){
        parent::init($db, $table, $params);
		$week = $this->generateWeekList();
		$current_week = date("W");
		$cart_data = new stdClass;
		$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"status_id","op":"eq","data":1}]}';
		$this->options['linktype'] = 'infoLink';
		$this->options['real_table'] = 'cycle';
        $this->options['list'] = array(
			'id'=>array('label'=>'ID', 'hidden'=>true),
			'name'=>array('width'=>290),
			'os_id'=>array('label'=>'OS', 'hidden'=>true),
			'board_type_id'=>array('label'=>'Board','hidden'=>true),
			'chip_id'=>array('hidden'=>true),
			'prj_id'=>array('width'=>160, 'label'=>'Project'),
			'rel_id'=>array('label'=>'Release', 'editrules'=>array('required'=>true)),
			'compiler_id'=>array('width'=>100),
			'cycle_type_id'=>array('label'=>'Cycle Type', 'hidden'=>true),
			'build_target_id'=>array('label'=>'Build Target'),
			'testcase_type_id'=>array('label'=>'Case Type', 'hidden'=>true),
			'group_id'=>array('label'=>'Group Type', 'hidden'=>true, 'formatoptions'=>array('value'=>$this->userAdmin->getGroups(true)), 
				'formatter'=>'select', 'searchoptions'=>array('value'=>$this->userAdmin->getGroups(true)), 'stype'=>'select'),
			'cycle_category_id'=>array('label'=>'Category', 'hidden'=>true),
			'cycle_status_id'=>array('label'=>'Cycle Status','hidden'=>true),
			'start_date'=>array('label'=>'Start Date','hidden'=>true),
			'end_date'=>array('label'=>'End Date','hidden'=>true),
			'tester_ids'=>array('label'=>'Tester', 'hidden'=>true, 'cart_db'=>'useradmin', 'cart_table'=>'users', 'cart_data'=>json_encode($cart_data)),
			'assistant_owner_id'=>array('label'=>'Assistant', 'hidden'=>true, 'editrules'=>array('required'=>false)),
			'test_env_id'=>array('label'=>'Env', 'hidden'=>true),
			'creater_id'=>array('label'=>'Creater', 'width'=>80),
			'description'=>array('width'=>200),
			'pass_cases'=>array('label'=>'Passed', 'hidden'=>true, 'excluded'=>true, 'width'=>85, 'search'=>false),
			'fail_cases'=>array('label'=>'Failed', 'hidden'=>true, 'excluded'=>true, 'width'=>40, 'search'=>false),
			'total_cases'=>array('label'=>'Total', 'excluded'=>true, 'width'=>50, 'search'=>false),
			'week'=>array('label'=>'Week', 'editrules'=>array('required'=>true), 'edittype'=>'select', 'type'=>'select', 'excluded'=>true, 'hidden'=>true, 'hidedlg'=>true, 'defval'=>$current_week, 'editoptions'=>array('value'=>$week)),
			'myname'=>array('label'=>'MyName', 'editrules'=>array('required'=>true), 'edittype'=>'text',  'type'=>'text', 'excluded'=>true, 'hidden'=>true, 'hidedlg'=>true),
			'*'=>array('hidden'=>true)
		);
		$this->options['query'] = array(
			'buttons'=>array(
				//'new'=>array('label'=>'New', 'onclick'=>'XT.go("/jqgrid/jqgrid/oper/information/db/xt/table/zzvw_cycle/element/0")', 'title'=>'Create New Cycle'),
				'query_new'=>array('label'=>'New', 'onclick'=>'XT.grid_query_add("mainContent", "'.$db.'", "'.$table.'")', 'title'=>'Create New Cycle'),
				'query_import'=>array('label'=>'Upload', 'onclick'=>'xt.zzvw_cycle.import()', 'title'=>'Import Cycle'),
			), 
			'normal'=>array('name'=>array('label'=>'Name'), 'chip_id', 'board_type_id', 'os_id', 'prj_id', 'rel_id', 'creater_id', 'testcase_type_id'), 
			'advanced'=>array('compiler_id', 'cycle_type_id', 'build_target_id', 'cycle_category_id', 'cycle_status_id', 'tester_ids')
		);
		$roleAndStatus = $this->roleAndStatus('cycle', 0, 0);
		if($roleAndStatus['role'] == 'guest'){
			unset($this->options['query']['buttons']);
		}
		$this->options['edit'] = array('group_id', 'chip_id', 'board_type_id', 'os_id', 'prj_id', 'rel_id', 'compiler_id', 
			'testcase_type_id', 'build_target_id', 'cycle_type_id', 'test_env_id', 'week'=>array('editable'=>true, 'defval'=>$current_week), 
			'myname'=>array('editable'=>true), 'name', 'description', 'tester_ids', 'assistant_owner_id', 'start_date', 'end_date', 'creater_id','tag'=>array('excluded=>true'), 'template'=>array('excluded'=>true)
		);
		if(!$this->userAdmin->isAdmin($this->userInfo->id) && !empty($this->params['id'])){
			$this->options['edit'] = array('group_id'=>array('editable'=>false), 'os_id'=>array('editable'=>false), 'board_type_id'=>array('editable'=>false), 'chip_id'=>array('editable'=>false), 'prj_id'=>array('editable'=>false), 
				'rel_id'=>array('editable'=>false), 'compiler_id', 'testcase_type_id', 'build_target_id', 'cycle_type_id', 'test_env_id', 'week'=>array('editable'=>true, 'defval'=>$current_week), 
				'myname'=>array('editable'=>true), 'name', 'description', 'tester_ids', 'assistant_owner_id', 'start_date', 'end_date', 'creater_id','tag'=>array('excluded=>true'), 'template'=>array('excluded'=>true)
			);	
		}
		$this->options['ver'] = '1.0';
		$this->options['gridOptions']['label'] = 'Cycle';
		$this->options['gridOptions']['inlineEdit'] = false;
		$this->options['gridOptions']['search'] = false;
		$this->options['navOptions']['refresh'] = false;
		$this->options['subGrid'] = array('expandField'=>'cycle_id', 'db'=>'xt', 'table'=>'zzvw_cycle_detail');
	}
	
	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		if(empty($components['order']))
			$components['order'] = 'id desc';
		return $components;
	}

	public function accessMatrix(){
		$access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = $access_matrix['row_owner']['all'] = $access_matrix['row_assistant_owner']['all'] = $access_matrix['tester']['all'] = true;
// print_r($access_matrix);
		// if(!empty($access_matrix['row_assistant_owner']))
			// $access_matrix['row_owner'] = $access_matrix['row_assistant_owner'];
		$access_matrix['tester']['view_edit_edit'] = $access_matrix['tester']['uploadfile'] = 
			$access_matrix['tester']['remove_combination'] = $access_matrix['tester']['cases_not_exist'] = 
			$access_matrix['tester']['set_group'] = $access_matrix['tester']['inside_freeze'] = 
			$access_matrix['tester']['freeze'] = $access_matrix['tester']['unfreeze'] = 
			$access_matrix['tester']['update'] = $access_matrix['tester']['download'] = false;
		$access_matrix['cycle_newer'] = $access_matrix['row_tester'] = $access_matrix['tester'];
		$access_matrix['cycle_newer']['view_edit_save'] = $access_matrix['cycle_newer']['view_edit_cancel'] = true;
		$access_matrix['Dev']['query'] = $access_matrix['Dev']['query_reset'] = 
			$access_matrix['Dev']['view_edit_export'] = $access_matrix['Dev']['export'] = true;
		return $access_matrix;
	}
	
	public function getRowRole($table_name = '', $id = 0){
		$role = parent::getRowRole($table_name, $id);
		if(empty($this->params['id'])){
			if(in_array('tester', $this->userInfo->roles))
				$role = 'cycle_newer';
		}
		return $role;
	}

	public function getButtons(){
		$buttons = array(
			'freeze'=>array('caption'=>'Freeze', 'title'=>'Freeze the selected cycles'),
			'clone'=>array('caption'=>'Clone', 'title'=>'Clone cycles'),
			'set_group'=>array('caption'=>'Set Group', 'title'=>'Set Group For Cycle')
		);
		$btns = parent::getButtons();
		unset($btns['activate']);
		unset($btns['inactivate']);
		return array_merge($btns, $buttons);
	}
	
	private function getStatistics(&$row, $stream_id, $test_env_id){
		$where = "cycle_id = {$row['id']}";		
		if($stream_id == '0')
			$where .= " AND codec_stream_id = 0";
		else{
			$where .= " AND codec_stream_id = ".$stream_id;
			if(!empty($test_env_id))
				$where .= " AND test_env_id=".$test_env_id;
		}
// print_r($where."\n");
		$where_pass = $where." AND result_type_id=".RESULT_TYPE_PASS;
		$where_fail = $where." AND result_type_id=".RESULT_TYPE_FAIL;
// print_r($where_pass."\n");
// print_r($where_fail."\n");
		$res = $this->tool->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE $where");
		$info = $res->fetch();
		$row['total_cases'] += $info['cases'];
// print_r($row['total_cases']."\n");
// print_r($info['cases']."\n");		
		$res = $this->tool->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE $where_pass");
		$info = $res->fetch();
		$row['pass_cases'] += $info['cases'];
		
		$res = $this->tool->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE $where_fail");
		$info = $res->fetch();
		$row['fail_cases'] += $info['cases'];
	}
	
	public function getMoreInfoForRow($row){
		$row['total_cases'] = 0;
		$row['pass_cases'] = 0;
		$row['fail_cases'] = 0;
		$res0 = $this->tool->query("SELECT test_env_id, codec_stream_id FROM cycle_detail WHERE cycle_id={$row['id']}");
		while($data = $res0->fetch()){
			// if(isset($list[$data['test_env_id']][$data['codec_stream_id']]))
				// continue;
			$list[$data['test_env_id']][] = $data['codec_stream_id'];
		}
// print_r($list);
		if(!empty($list)){
			foreach($list as $k=>$streamList){
				$streamList = array_unique($streamList);
// print_r(count($streamList)."\n");
				foreach($streamList as $v){
					if($v == '0')
						$this->getStatistics($row, 0, $k);
					else{
						$res1 = $this->tool->query("SELECT group_concat(distinct result_type_id) as result_type_id FROM cycle_detail".
							" WHERE  cycle_id={$row['id']} AND test_env_id = ".$k." AND codec_stream_id in ( ".$v." )");
						if($info1 = $res1->fetch()){
							$row['total_cases'] += 1;
							if($info1['result_type_id'] == '1')
								$row['pass_cases'] += 1;	
							else {
								$info1['result_type_id'] = ", ".$info1['result_type_id'];
								if(strpos($info1['result_type_id'], '2'))
									$row['fail_cases'] += 1;
							}
						}
					}
				}
			}
		}
		else
			$this->getStatistics($row, 0, 0);
		$passrate = 0;
		$color = 'red';
		if ($row['total_cases'] > 0){
			$passrate = number_format($row['pass_cases']/$row['total_cases'] * 100, 2);
			if ($passrate >= 80)
				$color = 'blue';
			else if ($passrate >= 60)
				$color = 'gray';
		}
		$row['pass_cases'] = sprintf("<span style='color:$color'>%-4d[%5.2f%%]</span>", $row['pass_cases'], $passrate);
		return $row;
	}
	
	protected function getEditFields($params){
		parent::getEditFields($params);
		if (!empty($this->options['edit']['group_id'])){
// print_r('group_id'."\n");
			$this->options['edit']['group_id']['edittype'] = 'select';
			$this->options['edit']['group_id']['editoptions']['value'] = $this->userAdmin->getGroups($this->userInfo->id);	
		}
		if (!empty($this->options['edit']['tag'])){
// print_r('group_id'."\n");
			$this->options['edit']['tag']['edittype'] = 'select';
			$res = $this->tool->query("select id, name, creater_id from tag where db_table = 'xt.codec_stream' order by name");
			$tag[0] = '';
			while($info = $res->fetch()){
				$userList = $this->userAdmin->getUserList(array('id'=>$info['creater_id']));
				$tag[$info['id']] = $info['name']."(-- by ".$userList[$info['creater_id']].")";
			}
			$this->options['edit']['tag']['editoptions']['value'] = $tag;	
		}
		if (!empty($this->options['edit']['template'])){
// print_r('group_id'."\n");
			$this->options['edit']['template']['edittype'] = 'select';
			$this->options['edit']['template']['editoptions']['value'] = array('0'=>'', '1'=>'BAT', '2'=>'FUNCTION', '3'=>'FULL');	
		}
		if(!empty($this->params['id']) && !is_array($this->params['id'])){
			if (!empty($this->options['edit']['assistant_owner_id'])){
				$res = $this->tool->query("select tester_ids from cycle where id = ".$this->params['id']);
				if($row = $res->fetch()){
					$userlist = $this->userAdmin->getUserList(array('id'=>$row['tester_ids']));
					$userlist[0] = '';
					$this->options['edit']['assistant_owner_id']['editoptions']['value'] = $userlist;	
				}
			}
		}
		return $this->options['edit'];
	}
	
	protected function getQueryFields($params){	
		parent::getQueryFields($params);
		//xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
		$this->options['query']['advanced']['cycle_status_id']['queryoptions']['value'] = '1';
		$this->options['query']['advanced']['tester_ids']['edittype'] = 'select';
// print_r($this->userInfo->nickname);
		$this->options['query']['normal']['creater_id']['queryoptions']['value'] = $this->userInfo->id;
		if(!empty($this->options['query']['normal']['rel_id'])){	
			$res = $this->tool->query("select id, name from rel where 1 order by id limit 0, 20");
			$rel[0] = '';
			while($info = $res->fetch())
				$rel[$info['id']] = $info['name'];
			$this->options['query']['normal']['rel_id']['searchoptions']['value'] = $rel;
		}
		return $this->options['query'];
	}
	
	private function generateWeekList($preWeek = 8, $postWeek = 10){
		$currentYear = (int)date('y');
		$currentWorkWeek = (int)date('W');
		$refData = array();
		for($i = $currentWorkWeek - $preWeek; $i < $currentWorkWeek + $postWeek; $i ++){
			$j = $i;
			$year = $currentYear;
			if ($i > 52){
				$j = $i - 52;
				$year = $currentYear + 1;
			}
			else if ($i <= 0){
				$j = $i + 52;
				$year = $currentYear - 1;
			}
			if ($j < 10)
				$refData[$i] = $year.'WK0'.$j;
			else
				$refData[$i] = $year.'WK'.$j;
		}
		return $refData;
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$userTable = $this->userAdmin->getUserTable();
		if ("$db.$table" == $userTable){
			$userList= $this->userAdmin->getUserList(array('blank'=>true));
			$columnDef['editoptions']['value'] = $columnDef['formatoptions']['value'] = $userList;
			$activeUserList= $this->userAdmin->getUserList(array('blank'=>true, 'isactive'=>true));
			$columnDef['searchoptions']['value'] = $userList;
			$columnDef['addoptions']['value'] = $activeUserList;
			if($columnDef['name'] == 'assistant_owner_id'){
				if($columnDef['defval'] == $this->userInfo->id)
					unset($columnDef['defval']);
				if($columnDef['editoptions']['defaultValue'] == $this->userInfo->id)
					unset($columnDef['editoptions']['defaultValue']);
			}
		}
		else{
			$this->tool->fillOptions($db, $table, $columnDef, false);
		}
	}
}
?>