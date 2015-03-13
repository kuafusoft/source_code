<?php
require_once('table_desc.php');

class xt_zzvw_cycle_detail extends table_desc{
	protected function init($db, $table, $params = array()){
        parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink';	
		$this->options['real_table'] = 'cycle_detail';
        $this->options['list'] = array(
			'id'=>array('formatter'=>'infoLink'),
			'c_f'=>array('hidden'=>true, 'excluded'=>true, 'edittype'=>'text', 'hidedlg'=>true),
			'cycle_id'=>array('hidden'=>true),
			'prj_id'=>array('label'=>'Prj', 'hidden'=>true),
			'chip_id'=>array('hidden'=>true),
			'board_type_id'=>array('label'=>'Board', 'hidden'=>true),
			'os_id'=>array('hidden'=>true),
			'd_code'=>array('label'=>'Testcase', 'editable'=>false, 'unique'=>false, 'formatter'=>'text_link', 'formatoptions'=>array('db'=>'xt', 'table'=>'testcase', 'newpage'=>true, 'data_field'=>'testcase_id', 'addParams'=>array('ver'=>'testcase_ver_id'))),
			'compiler_id'=>array('label'=>'IDE', 'hidden'=>true),
			'build_target_id'=>array('label'=>'Target', 'hidden'=>true),
			//'testcase_id'=>array('label'=>'Testcase', 'editable'=>false, 'unique'=>false, 'formatoptions'=>array('newpage'=>true, 'addParams'=>array('ver'=>'testcase_ver_id'))),
			'ver'=>array('label'=>'Ver', 'hidden'=>true, 'width'=>20, 'formatter'=>'text', 'editable'=>false),
			'summary'=>array('label'=>'Summary', 'width'=>65, 'editable'=>false),
			'testcase_type_id'=>array('label'=>'Case Type', 'hidden'=>true, 'editable'=>false),
			'testcase_category_id'=>array('label'=>'Category', 'hidden'=>true, 'editable'=>false),
			'testcase_module_id'=>array('label'=>'Module', 'hidden'=>true, 'editable'=>false),
			'testcase_testpoint_id'=>array('label'=>'Testpoint', 'hidden'=>true),
			//'result_type_type_id'=>array('hidden'=>true, 'hidedlg'=>true),
			'test_env_id'=>array('label'=>'Test Env', 'width'=>50),
			'result_type_id'=>array('label'=>'Result', 'width'=>30, 'editrules'=>array('required'=>true)), 
			'build_result_id'=>array('label'=>'B-Res', 'width'=>30, 'hidden'=>true, 'data_source_table'=>'result_type'),
			'testcase_priority_id'=>array('label'=>'Priority', 'hidden'=>true, 'cols'=>6, 'editable'=>false),
			'auto_level_id'=>array('label'=>'Auto Level', 'width'=>50, 'editable'=>false),
			'duration_minutes'=>array('hidden'=>true),
			'precondition'=>array('label'=>'Precondition', 'hidden'=>true, 'rows'=>8, 'editable'=>false),
			'objective'=>array('hidden'=>true),
			'steps'=>array('label'=>'Steps', 'hidden'=>true, 'rows'=>8, 'editable'=>false),
			'command'=>array('label'=>'CMDline', 'hidden'=>true, 'editable'=>false),
			'expected_result'=>array('label'=>'Expected Result', 'hidden'=>true, 'editable'=>false),
			'resource_link'=>array('label'=>'Resource Link', 'hidden'=>true, 'editable'=>false),
			'comment'=>array('label'=>'Comment', 'width'=>100),
			'issue_comment'=>array('label'=>'Issue Comment', 'hidden'=>true, 'editable'=>false),
			'defect_ids'=>array('label'=>'CRID/JIRA Key', 'formatter'=>'jira_link', 'required'=>false, 'width'=>50),
			'jira_key_ids'=>array('label'=>'JIRA Key', 'required'=>false, 'width'=>50, 'formatter'=>'jira_link', 'hidden'=>true),
			'deadline'=>array('hidden'=>true, 'required'=>false),
			'finish_time'=>array('label'=>'Finished', 'width'=>70),
			'tester_id'=>array('label'=>'Testor', 'width'=>35),
			'updater_id'=>array('label'=>'Updater', 'width'=>35, 'hidden'=>true),
			'isTester'=>array('excluded'=>true, 'hidden'=>true, 'hidedlg'=>true),
			'logFile'=>array('hidden'=>true, 'formatter'=>'log_link'),
			'act'=>array('label'=>'Tips', 'excluded'=>true, 'hidden'=>true, 'width'=>80, 'search'=>false),
			'creater_id'=>array('hidden'=>true, 'required'=>false),
		);
		$this->options['query'] = array(
			'normal'=>array('key'=>array('label'=>'Keyword'), 'result_type_id', 'tester_id', 'test_env_id', 'testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id',
				 'testcase_priority_id', 'auto_level_id', 'testcase_category_id', 'defect_ids', 'ver'=>array('excluded'=>true))//, 'group_by'=>array('label'=>'Group By', 'excluded'=>true))
		);
		$this->getCond();
		if(!empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id'){
			$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id', 'group'=>'group_id', 'testers'=>'tester_ids'));
			if(!empty($roleAndStatus['testers']))
				$this->params['sp_testers'] = explode(",", $roleAndStatus['testers']);
		}
		if(!empty($roleAndStatus['group'])){
			$group = $roleAndStatus['group'];
			if($group == 6 || $group == 7 || $group == 10){
				$this->options['query'] = array(
					'normal' =>array('key'=>array('label'=>'Keyword'), 'result_type_id', 'build_result_id'=>array('data_source_table'=>'result_type'), 'tester_id',
							'chip_id'=>array('label'=>'Chip', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'chip', 'label'=>'Chip')), 
							'board_type_id'=>array('label'=>'Board', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'board_type', 'label'=>'Board')),
							'os_id'=>array('label'=>'Os', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'os', 'label'=>'Os')),
							'testcase_priority_id',
							'prj_id'=>array('label'=>'Prj', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'prj', 'label'=>'Prj')), 
							'compiler_id'=>array('label'=>'Compiler', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'compiler', 'label'=>'Compiler')), 
							'build_target_id'=>array('label'=>'Target', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'build_target', 'label'=>'Target')),
							'test_env_id', 'testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id',
							'auto_level_id', 'testcase_category_id', 'defect_ids', 'ver'=>array('excluded'=>true)
					)
				);
			}
		}
		//$role = $roleAndStatus['role'];
		if(isset($this->params['container'])){
			if($this->params['container'] == 'div_new_case_add'){
				if(!empty($roleAndStatus['group'])){
					$group = $roleAndStatus['group'];
					if($group != 6 && $group != 7 && $group != 10){
						$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'os_id', 'chip_id', 'board_type_id', 
							'prj_id', 'creater_id'=>array('excluded'=>true), 'cycle_id', 'testcase_type_id', 'testcase_priority_id', 'result_type_id');
					}
					else{
						$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'os_id', 'chip_id', 'board_type_id', 
							'prj_id'=>array('label'=>'Project', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'prj', 'label'=>'Project')), 
							'compiler_id'=>array('label'=>'Compiler', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'compiler', 'label'=>'Compiler')), 
							'build_target_id'=>array('label'=>'Build Target', 'type'=>'single_multi', 'init_type'=>'single', 
								'single_multi'=>array('db'=>$db, 'table'=>'build_target', 'label'=>'Build Target')), 
							'creater_id'=>array('excluded'=>true), 'cycle_id', 'testcase_type_id', 'testcase_priority_id', 'result_type_id');
					}
				}
				$this->options['query']['advanced'] = array('testcase_category_id', 'testcase_module_id', 'testcase_testpoint_id', 'test_env_id',
					'auto_level_id', 'defect_ids', 'tester_id');
			}
			else if(stripos($this->params['container'], "test_history") !== false){
				$this->options['query']['normal'] = 
					array('os_id', 'chip_id', 'board_type_id', 'prj_id', 'compiler_id', 'build_target_id', 'result_type_id', 
						'cycle_id', "defect_ids", 'tester_id');
				unset($this->options['query']['advanced']);
			}
		}
		if(isset($roleAndStatus['status'])){
			$status = $roleAndStatus['status'];
			if($status == CYCLE_STATUS_ONGOING){
				$this->options['query']['buttons'] = array(	
					'query_add'=>array('label'=>'Add Cases', 'title'=>'Add Cases To The Cycle'),
					'query_remove'=>array('label'=>'Remove', 'title'=>'Remove Cases From This Cycle')
				);
			}
		}
		$this->options['edit'] = array('d_code', 'ver', 'summary', 'precondition', 'steps', 'expected_result', 'auto_level_id', 'test_env_id', 
			'result_type_id', 'defect_ids', 'comment', 'issue_comment', 'new_issue_comment'=>array('edittype'=>'textarea'), 'duration_minutes');	
			
		$this->options['gridOptions']['label'] = 'cycle cases';
		$this->options['gridOptions']['inlineEdit'] = false;
		$this->options['gridOptions']['search'] = false;
		$this->options['navOptions']['refresh'] = false;
		// $this->options['gridOptions']['grouping'] = true;
		// $this->options['gridOptions']['groupingView'] = array(
			// 'groupField'=>array('chip_id', 'os_id'), 
			// //'groupText'=>array("<span class='group-span'><input type='checkbox' class='grouping'><label class='grouping-label'> </label></span><span class='groupText'> {0} - {1} Records(s) </span>"),         
			// 'groupColumnShow'=>array(false, false), 
			// 'groupCollapse'=>true
			// );
        $this->options['ver'] = '1.0';
    }
	
	protected function getRoleRow($table_name, $id){
		$row = array();
		if(empty($id)){
// print_r($this->params);
			if(!empty($this->params['cond']) && !empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id' ){
				$res = $this->tool->query("SELECT * FROM cycle WHERE id IN ({$this->params['cond']['value']})");
			}
		}
		else{
			$res = $this->tool->query("SELECT * FROM cycle_detail LEFT JOIN cycle ON cycle.id = cycle_detail.cycle_id WHERE cycle_detail.id IN ($id)");
		}
		if(!empty($res))
			$row = $res->fetch();
		return $row;
	}

	public function accessMatrix(){
		$access_matrix = parent::accessMatrix();

		$access_matrix['all']['all'] = false; //禁止一切
		$access_matrix['admin']['all'] = $access_matrix['tester']['all'] = $access_matrix['row_tester']['all'] = 
			$access_matrix['row_owner']['all'] = $access_matrix['row_assistant_owner']['all'] = true; //对某些role允许一切
		
		//对被允许的role进行微调
		$access_matrix['tester']['query_add'] = $access_matrix['tester']['query_update'] = $access_matrix['tester']['query_remove'] = 
			$access_matrix['tester']['set_result'] = $access_matrix['tester']['set_build_result'] = $access_matrix['tester']['set_crid'] =
			$access_matrix['tester']['set_tester'] = $access_matrix['tester']['removecase'] = $access_matrix['tester']['update_ver'] =
			$access_matrix['tester']['add_del_env'] = $access_matrix['tester']['add_del_trickmode'] = 
			$access_matrix['tester']['view_edit_edit'] = $access_matrix['tester']['view_edit_save'] = $access_matrix['tester']['view_edit_cloneit'] = false;
			
		$access_matrix['row_tester']['query_add'] = $access_matrix['row_tester']['query_update'] = $access_matrix['row_tester']['query_remove'] = 
			$access_matrix['row_tester']['set_tester'] = $access_matrix['row_tester']['removecase'] = $access_matrix['row_tester']['update_ver'] =
			$access_matrix['row_tester']['add_del_env'] = $access_matrix['row_tester']['add_del_trickmode'] = false;
			
		$access_matrix['Dev']['query'] = $access_matrix['Dev']['query_reset'] = true;
		return $access_matrix;
	}
	
	protected function getQueryFields(){	
		parent::getQueryFields();
		if(!empty($this->options['query']['normal']['testcase_priority_id'])){	
			$this->options['query']['normal']['testcase_priority_id']['edittype'] ='checkbox';
			$this->options['query']['normal']['testcase_priority_id']['cols'] ='6';
		}
		if(!empty($this->options['query']['normal']['result_type_id'])){	
			$this->options['query']['normal']['result_type_id']['searchoptions']['value'] .= ";-1:==Blank=";
		}
		if(!empty($this->options['query']['normal']['tester_id'])){	
			$this->options['query']['normal']['tester_id']['searchoptions']['value'] .= ";-1:==Blank=";
		}
		if(!empty($this->options['query']['normal']['ver'])){	
			$this->options['query']['normal']['ver']['edittype'] ='select';
			$this->options['query']['normal']['ver']['searchoptions']['value'] = array('0'=>'', '1'=>'case need to be updated', '2'=>'case not belong to this prj');
		}
		if(!empty($this->options['query']['normal']['creater_id']))
			$this->options['query']['normal']['creater_id']['queryoptions']['value'] =$this->userInfo->id;

		if(!empty($this->params['cond']['value'])){
			$res = $this->tool->query("select prj_ids, compiler_ids, build_target_ids, creater_id, assistant_owner_id, tester_ids".
				" from cycle where id=".$this->params['cond']['value']);
			$info = $res->fetch();
			$currentUser = $this->userInfo->id;
			$isAdmin = false;
			if(!empty($this->userInfo->isAdmin))
				$isAdmin = true;
			if(!$isAdmin && $info['creater_id'] != $currentUser && $info['assistant_owner_id'] != $currentUser){
				$info['tester_ids'] = explode(",", $info['tester_ids']);
				if(in_array($currentUser, $info['tester_ids'] )){
					if(!empty($this->options['query']['normal']['tester_id']))
						$this->options['query']['normal']['tester_id']['queryoptions']['value'] = $currentUser;
				}
			}
			$cart_data = new stdClass;
			$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$info['prj_ids'].'"}]}';
			if(!empty($this->options['query']['advanced']['prj_id']['single_multi']))
				$this->options['query']['advanced']['prj_id']['single_multi']['data'] = json_encode($cart_data);
			$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$info['compiler_ids'].'"}]}';
			if(!empty($this->options['query']['advanced']['compiler_id']['single_multi']))
				$this->options['query']['advanced']['compiler_id']['single_multi']['data'] = json_encode($cart_data);
			$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$info['build_target_ids'].'"}]}';
			if(!empty($this->options['query']['advanced']['build_target_id']['single_multi']))
				$this->options['query']['advanced']['build_target_id']['single_multi']['data'] = json_encode($cart_data);
			if($info['prj_ids']){
				$res = $this->tool->query("select group_concat(distinct os_id) as os_ids, group_concat(distinct chip_id) as chip_ids, group_concat(distinct board_type_id) as board_type_ids".
					" from prj where id in ({$info['prj_ids']})");
				$row = $res->fetch();
				$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$row['os_ids'].'"}]}';
				if(!empty($this->options['query']['advanced']['os_id']['single_multi'])){
					$this->options['query']['advanced']['os_id']['single_multi']['data'] = json_encode($cart_data);
				}
				$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$row['chip_ids'].'"}]}';
				if(!empty($this->options['query']['advanced']['chip_id']['single_multi'])){
					$this->options['query']['advanced']['chip_id']['single_multi']['data'] = json_encode($cart_data);
				}
				$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$row['board_type_ids'].'"}]}';
				if(!empty($this->options['query']['advanced']['board_type_id']['single_multi'])){
					$this->options['query']['advanced']['board_type_id']['single_multi']['data'] = json_encode($cart_data);
				}
			}
		}	
		
		
		return $this->options['query'];
	}
	
	public function getButtons(){
		$btns = parent::getButtons();
		unset($btns['add']);
	//	unset($btns['tag']);
	//	unset($btns['removeFromTag']);
		if(!empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id'){
			$cycle = '';
			$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id'));
			//$role = $roleAndStatus['role'];
			if(isset($roleAndStatus['status'])){
				$status = $roleAndStatus['status'];
				if($status == CYCLE_STATUS_ONGOING){
					$newBtns = array(
						'set_result'=>array('caption'=>'Set Result', 'title'=>'Set the test results'),
						'set_build_result'=>array('caption'=>'Set Build Result', 'title'=>'Set the Build results'),
						'set_crid' => array('caption'=>'Set CRID', 'title'=>'set CRID'),
						'set_tester'=>array('caption'=>'Assign Tester', 'title'=>'Set the tester for the cases'),
						'removecase' => array('caption'=>'Remove Cases', 'title'=>'Delete records in cycle'),
						'update_ver'=> array('caption'=>'Update Ver', 'title'=>'Update The Version To Latest'),
						'add_del_env'=>array('caption'=>'Add (or Del) Test Env', 'title'=>'Add or Del Env'),
					);
					$btns = array_merge($btns, $newBtns);	
				}	
			}
		}
		return $btns;
	}
	
	protected function getCond(){
		$cond['field'] = 'cycle_id';
		if(!empty($this->params['parent'])){
			// if(!empty($this->params['container'])){
				// if(stripos($this->params['container'], 'cycle_detail') !== false || stripos($this->params['container'], 'cycle_stream') !== false)
					// $cond['value'] = $this->params['parent'];
			// }
			// else if($this->params['table'] == 'zzvw_cycle_detail' || $this->params['table'] == 'zzvw_cycle_detail_stream')
			if(!empty($this->params['filters'])){
				$filter = json_decode($this->params['filters']);
// print_r($filter);
				foreach($filter->rules as $k=>$v){
					if($v->field == 'cycle_id')
						$cond['value'] = $v->data;
				}
			}
			else
				$cond['value'] = $this->params['parent'];
		}
		else if(!empty($this->params['cycle_id'])){
			$cond['value'] = $this->params['cycle_id'];
		}
		else if(!empty($this->params['hidden'])){
			$hidden = json_decode($this->params['hidden']);
			foreach($hidden as $k=>$v){
				if($k == $cond['field'])
					$cond['value'] = $v;
			}
		}
		else if(!empty($this->params['filters'])){
			$filter = json_decode($this->params['filters']);
// print_r($filter);
			foreach($filter->rules as $k=>$v){
				if($v->field == 'cycle_id')
					$cond['value'] = $v->data;
			}
		}
		else if(!empty($this->params['id'])){
			if(is_array($this->params['id']))
				$sql = "select cycle_id from ".$this->get('real_table')." where id in (".implode(", ", $this->params['id']).")";
			else
				$sql = "select cycle_id from ".$this->get('real_table')." where id = ".$this->params['id'];
			$res = $this->tool->query($sql);
			if($info = $res->fetch())
				$cond['value'] = $info['cycle_id'];
		}
		$this->params['cond'] = $cond;
	}
	
	public function getOptions($trimed = true, $params = Array()){
		$status = CYCLE_STATUS_FROZEN;
		if(!empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id'){
			$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id'));
			if(isset($roleAndStatus['status']))
				$status = $roleAndStatus['status'];
		}
		parent::getOptions();
		if($status == CYCLE_STATUS_ONGOING){	
			$colModel = $this->options['gridOptions']['colModel'];
			foreach($colModel as $k=>$m){	
				switch($m['name']){
					case 'result_type_id':
						$colModel[$k]['formatter'] = 'resultLink';
						break;
						
					case 'tester_id':
						$colModel[$k]['formatter'] = 'testorLink';
						break;
						
					case 'build_result_id':
						$colModel[$k]['formatter'] = 'bResultLink';
						break;
				}
			}
			$this->options['gridOptions']['colModel'] = $colModel;
		}
		
		unset($this->options['tags']);
		return $this->options;
	}
	
	protected function handleFillOptionCondition(){
		$where = '';
		if(!empty($this->params['searchConditions'])){
			$searchConditions = $this->params['searchConditions'];
			foreach($searchConditions as $condition){
				switch($condition['field']){
					case 'cycle_id':
						$where = $condition;
					case 'testcase_id':
						$where = $condition;
						break;
				}
			}
		}
		else if(!empty($this->params['parent'])){
			$where = array('field'=>'', 'op'=>'=', 'value'=>'');
			if(!empty($this->params['container'])){
				if(stripos($this->params['container'], 'cycle_detail') !== false){
					$where['field'] = 'cycle_id';
					$where['value'] = $this->params['parent'];
				}
				else if(stripos($this->params['container'], 'test_history') !== false){
					$where['field'] = 'testcase_id';
					$where['value'] = $this->params['parent'];
				}
			}
		}
// print_r($this->params);
		if(!empty($where['value']) && !empty($where['field'])){
			$wheres = " where {$where['field']} = {$where['value']}";
			$sql = "select group_concat(distinct cycle_id) as cycle_id, group_concat(distinct os_id) as os_id, 
				group_concat(distinct chip_id) as chip_id, group_concat(distinct board_type_id) as board_type_id,
				group_concat(distinct prj_id) as prj_id, group_concat(distinct compiler_id) as compiler_id, 
				group_concat(distinct build_target_id) as build_target_id, group_concat(distinct testcase_type_id) as testcase_type_id, 
				group_concat(distinct test_env_id) as test_env_id, group_concat(distinct auto_level_id) as auto_level_id, 
				group_concat(distinct testcase_priority_id) as testcase_priority_id, group_concat(distinct tester_id) as tester_id, 
				group_concat(distinct testcase_category_id) as testcase_category_id, group_concat(distinct testcase_module_id) as testcase_module_id, 
				group_concat(distinct testcase_testpoint_id) as testcase_testpoint_id, group_concat(distinct creater_id) as creater_id 
				from ".$this->get('table')." $wheres";	
// print_r($sql);				
			$res = $this->tool->query($sql);
			if($row = $res->fetch()){
				$condition = array('field'=>'id', 'op'=>'in');
			//	$condition['value'] = $row['cycle_id'];
			//	$this->fillOptionConditions['cycle_id'] = array($condition);
				$condition['value'] = $row['os_id'];
				$this->fillOptionConditions['os_id'] = array($condition);
				$condition['value'] = $row['chip_id'];
				$this->fillOptionConditions['chip_id'] = array($condition);
				$condition['value'] = $row['board_type_id'];
				$this->fillOptionConditions['board_type_id'] = array($condition);
				$condition['value'] = $row['prj_id'];
				$this->fillOptionConditions['prj_id'] = array($condition);
				$condition['value'] = $row['compiler_id'];
				$this->fillOptionConditions['compiler_id'] = array($condition);
				$condition['value'] = $row['build_target_id'];
				$this->fillOptionConditions['build_target_id'] = array($condition);
				$condition['value'] = $row['testcase_type_id'];
				$this->fillOptionConditions['testcase_type_id'] = array($condition);
				$condition['value'] = $row['testcase_priority_id'];
				$this->fillOptionConditions['testcase_priority_id'] = array($condition);
				$condition['value'] = $row['testcase_module_id'];
				$this->fillOptionConditions['testcase_module_id'] = array($condition);
				$condition['value'] = $row['testcase_testpoint_id'];
				$this->fillOptionConditions['testcase_testpoint_id'] = array($condition);
				$condition['value'] = $row['testcase_category_id'];
				$this->fillOptionConditions['testcase_category_id'] = array($condition);
				$condition['value'] = $row['test_env_id'];
				$this->fillOptionConditions['test_env_id'] = array($condition);
				$condition['value'] = $row['auto_level_id'];
				$this->fillOptionConditions['auto_level_id'] = array($condition);
				$condition['value'] = $row['tester_id'];
				$this->fillOptionConditions['tester_id'] = array($condition);
				$condition['value'] = $row['creater_id'];
				$this->fillOptionConditions['creater_id'] = array($condition);
			}
			$condition = array('field'=>'id', 'op'=>'in');
			$c_sql = "select distinct cycle_id from ".$this->get('table')." $wheres";
			$c_res = $this->tool->query($c_sql);
			while($row = $c_res->fetch()){
				$cycle_id[] = $row['cycle_id'];
			}
			if(!empty($cycle_id)){
				$condition['value'] = implode(",", $cycle_id);
				$this->fillOptionConditions['cycle_id'] = array($condition);	
			}
			if($where['field'] == 'cycle_id'){
				$res = $this->tool->query("select tester_ids from cycle where id = ".$where['value']);
				if($info = $res->fetch()){
					$info['tester_ids'] = explode(",", $info['tester_ids']);
					foreach($info['tester_ids'] as $k=>$tester_id){
						if(empty($tester_id))
							unset($info['tester_ids'][$k]);
					}
					$condition['value'] = implode(",", $info['tester_ids']);
					$this->fillOptionConditions['tester_id'] = array($condition);
				}
			}
		}
	}
	
	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		if(empty($components['order'])){
			$components['order'] = 'id desc';
			if(!empty($params['parent'])){
				$res = $this->tool->query("SELECT group_id from cycle where id=".$params['parent']);
				if($row = $res->fetch()){
					if(6 == $row['group_id'] || 7 == $row['group_id'] || 10 == $row['group_id'] )
						$components['order'] = 'chip_id, os_id, d_code, id desc';
				}
			}
		}
		return $components;
	}
	
	protected function getSpecialFilters(){
		$special = array('ver');
		return $special;
	}
	
	protected function specialSql($special, &$ret){
		if(!empty($this->params['prj_id'])){
			if(is_array($this->params['prj_id']) && count($this->params['prj_id']) == 1)
				$prj_id = $this->params['prj_id'][0];
			else
				$prj_id = $this->params['prj_id'];
		}
// print_r($prj_id);
		if(!empty($prj_id)){
			if(count($special) == 1 && !empty($special)){
				if($special[0]['field'] == 'ver'){
					if($special[0]['value'] == 1){
						$ret['main']['from'] .= " LEFT JOIN prj_testcase_ver ON zzvw_cycle_detail.testcase_id=prj_testcase_ver.testcase_id".
							" LEFT JOIN testcase_ver ON testcase_ver.id = prj_testcase_ver.testcase_ver_id";
						$ret['where'] .= " AND zzvw_cycle_detail.prj_id = prj_testcase_ver.prj_id".
							" AND zzvw_cycle_detail.testcase_ver_id != prj_testcase_ver.testcase_ver_id".
							" AND testcase_ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")".
							" AND prj_testcase_ver.prj_id = {$prj_id} AND zzvw_cycle_detail.prj_id = {$prj_id}";
					}
					else if($special[0]['value'] == 2){
						$ret['where'] .= " AND cycle_detail.testcase_ver_id NOT IN (SELECT testcase_ver_id from prj_testcase_ver where prj_id={$prj_id}".
							" AND cycle_detail.prj_id = {$prj_id}";
					}
				}
			}
		}
	}
	
	public function getMoreInfoForRow($row){
		$row['isTester'] = false;
		if($row['tester_id'] == $this->userInfo->id)
			$row['isTester'] = true;
		else if(!empty($this->params['role']) && $this->params['role'] ==  'row_owner')
			$row['isTester'] = true;
		else if($row['creater_id'] == $this->userInfo->id)
			$row['isTester'] = true;
		else if($row['tester_id'] == 132 && !empty($this->params['sp_testers']) && in_array($this->userInfo->id, $this->params['sp_testers']))
			$row['isTester'] = true;
		else {
			if(in_array('admin', $this->userInfo->roles) || in_array('assistant_admin', $this->userInfo->roles))
				$row['isTester'] = true;
		}
		
		//$row['log'] = 'log download';
		return $row;
	}
}

?>