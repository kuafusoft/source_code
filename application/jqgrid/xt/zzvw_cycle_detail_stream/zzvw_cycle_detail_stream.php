<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/zzvw_cycle_detail.php');

class xt_zzvw_cycle_detail_stream extends xt_zzvw_cycle_detail{
	protected function init($db, $table, $params = array()){
        parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink';
		$this->options['real_table'] = 'cycle_detail';
        $this->options['list'] = array(
			'id',
			'c_f'=>array('hidden'=>true, 'excluded'=>true, 'edittype'=>'text', 'hidedlg'=>true, 'editable'=>false),
			'cycle_id'=>array('hidden'=>true),
			// 'codec_stream_id'=>array('label'=>'S-ID', 'formatoptions'=>array('newpage'=>true)),
			'd_code'=>array('label'=>'S-ID', 'editable'=>false, 'unique'=>false, 'formatter'=>'text_link', 'formatoptions'=>array('db'=>'xt', 'table'=>'codec_stream', 'newpage'=>true, 'data_field'=>'codec_stream_id')),
			'name'=>array('label'=>'Name', 'editable'=>false, 'unique'=>false, 'width'=>65, 'formatter'=>'text', 'formatoptions'=>array('db'=>'xt', 'table'=>'codec_stream', 'newpage'=>true, 'data_field'=>'codec_stream_id')),
			'location'=>array('label'=>'Location', 'width'=>100, 'editable'=>false),
			'testcase_priority_id'=>array('label'=>'Priority', 'hidden'=>true, 'cols'=>6, 'editable'=>false),
			'codec_stream_type_id'=>array('label'=>'Type', 'hidden'=>true, 'editable'=>false),
			'codec_stream_format_id'=>array('label'=>'Format', 'hidden'=>true, 'editable'=>false),
			'test_env_id'=>array('label'=>'Test-Env', 'width'=>50, 'editrules'=>array('required'=>true)),
			'result_type_id'=>array('label'=>'Result', 'width'=>30, 'editrules'=>array('required'=>true)),
			'codec_stream_result'=>array('label'=>'S-Res', 'excluded'=>true, 'width'=>80, 'search'=>false, 'editable'=>false),			
			'duration_minutes'=>array('hidden'=>true),
			'precondition'=>array('label'=>'Precondition', 'hidden'=>true, 'rows'=>8, 'editable'=>false),
			'steps'=>array('label'=>'Steps', 'hidden'=>true, 'rows'=>8, 'editable'=>false),
			'command'=>array('label'=>'CMDline', 'hidden'=>true, 'editable'=>false, 'excluded'=>true),
			'comment'=>array('label'=>'CR Comment', 'width'=>100),
			'issue_comment'=>array('label'=>'Issue Comment', 'hidden'=>true, 'editable'=>false),
			'defect_ids'=>array('label'=>'CRID', 'formatter'=>'text', 'editrules'=>array('required'=>false), 'width'=>50),
			'deadline'=>array('hidden'=>true, 'required'=>false),
			'finish_time'=>array('label'=>'Finished', 'width'=>70),
			'tester_id'=>array('label'=>'Testor', 'width'=>35),
			'isTester'=>array('excluded'=>true, 'hidden'=>true, 'hidedlg'=>true),
			'duration'=>array('label'=>'Duration', 'hidden'=>true, 'width'=>35),
			'a_duration'=>array('label'=>'Audio Duration', 'width'=>35),
			'v_duration'=>array('label'=>'Video Duration', 'width'=>35),
			'log'=>array('excluded'=>true, 'hidden'=>true, 'editoptions'=>array('defval'=>'logFile'), 'formatter'=>'log_link'),
		);
		$this->options['query'] = array(
			'cols'=>4,			
			'normal'=>array('key'=>array('label'=>'Keyword'), 'codec_stream_type_id', 'codec_stream_format_id', 'test_env_id', 'testcase_priority_id',
				'result_type_id', 'tester_id', 'defect_ids')
		);
		if(!empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id')
			$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id'));
			//$role = $roleAndStatus['role'];
		if(isset($roleAndStatus['status'])){
			$status = $roleAndStatus['status'];
			if($status == CYCLE_STATUS_ONGOING){
				$this->options['query']['buttons'] = array(
					'query_add'=>array('label'=>'Add Cases', 'title'=>'Add Cases To The Cycle'),
					'query_remove'=>array('label'=>'Remove', 'title'=>'Remove Cases From This Cycle'),
					'query_update'=>array('label'=>'Update All Tricmodes', 'title'=>'Update All Trickmodes For this Cycle')
				);
			}
		}
		if(!empty($this->params['container'])){
			if($this->params['container'] == 'div_new_case_add'){
				$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'os_id', 'chip_id', 'board_type_id', 
					'prj_id', 'creater_id'=>array('excluded'=>true), 'cycle_id', 'codec_stream_type_id', 'codec_stream_format_id', 'tester_id', 'testcase_priority_id', 'result_type_id');
				unset($this->options['query']['advanced']);
			}
			else if(stripos($this->params['container'], "test_history") !== false){
				$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'os_id', 'chip_id', 'board_type_id', 
					'prj_id', 'creater_id'=>array('excluded'=>true), 'cycle_id', 'tester_id', 'result_type_id');
			}
		}
		$this->options['edit'] = array('d_code', 'name', 'testcase_priority_id','precondition', 'steps', 'test_env_id', 'result_type_id', 
			'defect_ids', 'comment', 'issue_comment', 'new_issue_comment'=>array('edittype'=>'textarea'), 'duration_minutes');		
			
		$this->options['gridOptions']['label'] = 'cycle cases';
		$this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'id', 'db'=>'xt', 'table'=>'zzvw_subgrid_cycle_detail');
		$this->options['gridOptions']['inlineEdit'] = false;
		$this->options['gridOptions']['search'] = false;
		$this->options['navOptions']['refresh'] = false;
        $this->options['ver'] = '1.0';
		$this->getCond();
    }
	
	public function accessMatrix(){
		$access_matrix = parent::accessMatrix();
		$access_matrix['row_tester']['set_build_result'] = false;
		return $access_matrix;
	}
	
	protected function getQueryFields(){	
		parent::getQueryFields();
		if(!empty($this->options['query']['normal']['testcase_priority_id'])){
			$this->options['query']['normal']['testcase_priority_id']['edittype'] = 'checkbox';
			$this->options['query']['normal']['testcase_priority_id']['cols'] ='6';
		}
		return $this->options['query'];
	}
	
	public function getButtons(){
		$btns = parent::getButtons();
		if(!empty($this->params['cond']['value']) && $this->params['cond']['field'] == 'cycle_id'){
			$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id'));
//				$roleAndStatus = $this->roleAndStatus($cond['value']);
			$role = $roleAndStatus['role'];
			if(isset($roleAndStatus['status'])){
				$status = $roleAndStatus['status'];
				if($status == CYCLE_STATUS_ONGOING){
					unset($btns['update_ver']);
					$btns['add_del_trickmode'] = array('caption'=>'Add (or Del) TrickModes', 'title'=>'Add or Del TrickModes');
				}
			}				
			if(!empty($btns['set_build_result']))
				unset($btns['set_build_result']);
		}
		return $btns;
	}
	
	public function getMoreInfoForRow($row){
		$row['codec_stream_result'] = 'does not exsit';
		if(isset($row['c_f']) && $row['c_f'] == 1){
			//看如果说能够用group cat的话，如果说是有fail的话，就将result_type置空
			//如果说全pass的话，就填原值，我觉得这个比较合理一些
			$res = $this->tool->query("SELECT id, name FROM result_type");
			$result_type[0] = 'Blank';
			while($info = $res->fetch())
				$result_type[$info['id']] = $info['name'];
			$res0 = $this->tool->query("SELECT comment, defect_ids, result_type_id".
				" FROM cycle_detail WHERE codec_stream_id=".$row['codec_stream_id'].
				" AND cycle_id=".$row['cycle_id']." AND test_env_id=".$row['test_env_id']);
			$row['defect_ids'] = null;
			while($info = $res0->fetch()){
				$d['result_type_id'][] = $info['result_type_id'];
				if(!empty($info['defect_ids']) && $info['defect_ids'] != '')
					$d['defect_ids'][] = $info['defect_ids'];
				if(!empty($info['comment']) && $info['comment'] != '')
					$d['comment'][] = $info['comment'];
			}
			if(isset($d['comment'])){
				if(count($d['comment']) < count($d['result_type_id']))
					$row['comment'] = 'Pls Check Specific Trickmode Comment In Subgrid';
				else if(count($d['comment']) == count($d['result_type_id'])){
					if(array_unique($d['comment']) == 1)
						$row['comment'] = $d['comment'][0];
				}
			}
			if(isset($d['defect_ids']))
				$row['defect_ids'] = implode(", ", array_unique($d['defect_ids']));
			if(!empty($d['result_type_id'])){
				$d['result_type_id'] = array_unique($d['result_type_id']);
				if(count($d['result_type_id']) == 1){
					$row['codec_stream_result'] = 'All '.$result_type[$d['result_type_id'][0]];
					if($d['result_type_id'][0] == 2)
						$row['result_type_id'] = 112;
				}
				else{
					// $d['result_type_id'] = explode(",", $d['result_type_id']);
// print_r($d['result_type_id']);
					unset($result_type['1']);
					$special = array('0'=>'Blank', '2'=>'Fail');
					foreach($result_type as $k=>$v){
// print_r($k);
						if(in_array($k, $d['result_type_id'])){
							$row['result_type_id'] = 100 + $k;// Testing
							$row['codec_stream_result'] = 'Has '.$v;
							if(in_array('1', $d['result_type_id']))
								$row['codec_stream_result'] = 'Has '.$v.' & Pass';
							if($k != '2'){
								if(in_array('2', $d['result_type_id']))
									$row['codec_stream_result'] = 'Has '.$v.' & Fail';
							}
							if($k == 0 || $k = 2)
								break;
						}
					}
				}
			}
			$res = $this->tool->query("select env_item_ids from test_env where id=".$row['test_env_id']);
// print_r($row['test_env_id']."zzz");
			if($info = $res->fetch()){
				$env_item = false;
				if(!empty($info['env_item_ids'])){
					$res0 = $this->tool->query("select steps, precondition, command from stream_steps".
						" where codec_stream_type_id=".$row['codec_stream_type_id']." and env_item_id in (".$info['env_item_ids'].")");
					if($info0 = $res0->fetch()){		
						$env_item = true;
						$row['steps'] = $info0['steps'];
						$row['precondition'] = $info0['precondition'];
						$row['command'] = $info0['command'];
					}
				}
				if(!$env_item){
					$result = $this->tool->query("select os.name from prj left join os on prj.os_id = os.id where prj.id = ".$row['prj_id']);
					$os = $result->fetch();
					if(stripos(strtolower($os['name']), "android") !== false)
						$os_name = 'Android';
					else if(stripos(strtolower($os['name']), "linux") !== false)
						$os_name = 'Linux';
					if(!empty($os_name)){
						$res1 = $this->tool->query("select steps.steps, steps.precondition, steps.command from stream_steps steps".
							" left join stream_tools tools on steps.env_item_id = tools.env_item_id".
							" where tools.codec_stream_format_id=".$row['codec_stream_format_id']." and tools.os='".$os_name."'");
						if($info1 = $res1->fetch()){
							$row['steps'] = $info1['steps'];
							$row['precondition'] = $info1['precondition'];
							$row['command'] = $info1['command'];
						}
					}
				}
			}
		}
		$row['isTester'] = false;
		if($row['tester_id'] == $this->userInfo->id)
			$row['isTester'] = true;
		else if($this->params['role'] ==  'row_owner')
			$row['isTester'] = true;
		else if($row['creater_id'] == $this->userInfo->id)
			$row['isTester'] = true;
		else {
			if(in_array('admin', $this->userInfo->roles) || in_array('assistant_admin', $this->userInfo->roles))
				$row['isTester'] = true;

		}
		$row['log'] = 'log download';
		return $row;
	}
	
	protected function handleFillOptionCondition(){
		$where = '';
		if(!empty($this->params['searchConditions'])){
			$searchConditions = $this->params['searchConditions'];
			foreach($searchConditions as $condition){
				switch($condition['field']){
					case 'cycle_id':
						$where = $condition;
					case 'codec_stream_id':
						$where = $condition;
						break;
				}
			}
		}
		else if(!empty($this->params['parent'])){
			$where = array('field'=>'', 'op'=>'=', 'value'=>'');
			if(!empty($this->params['container'])){
				if(stripos($this->params['container'], 'cycle_stream') !== false){
					$where['field'] = 'cycle_id';
					$where['value'] = $this->params['parent'];
				}
				else if(stripos($this->params['container'], 'test_history') !== false){
					$where['field'] = 'codec_stream_id';
					$where['value'] = $this->params['parent'];
				}
			}
		}
		if(!empty($where['value']) && !empty($where['field'])){
			$wheres = " where {$where['field']} = {$where['value']}";
			$sql = "select group_concat(distinct os_id) as os_id, 
				group_concat(distinct chip_id) as chip_id, group_concat(distinct board_type_id) as board_type_id, group_concat(distinct prj_id) as prj_id,
				group_concat(distinct codec_stream_type_id) as codec_stream_type_id, group_concat(distinct creater_id) as creater_id,
				group_concat(distinct test_env_id) as test_env_id, group_concat(distinct codec_stream_format_id) as codec_stream_format_id, 
				group_concat(distinct testcase_priority_id) as testcase_priority_id, group_concat(distinct tester_id) as tester_id
				from ".$this->get('table')." $wheres";			
			$res = $this->tool->query($sql);
			if($row = $res->fetch()){
				$condition = array('field'=>'id', 'op'=>'in');
				//$condition['value'] = $row['cycle_id'];
				//$this->fillOptionConditions['cycle_id'] = array($condition);
				$condition['value'] = $row['os_id'];
				$this->fillOptionConditions['os_id'] = array($condition);
				$condition['value'] = $row['chip_id'];
				$this->fillOptionConditions['chip_id'] = array($condition);
				$condition['value'] = $row['board_type_id'];
				$this->fillOptionConditions['board_type_id'] = array($condition);
				$condition['value'] = $row['prj_id'];
				$this->fillOptionConditions['prj_id'] = array($condition);
				// $condition['value'] = $row['compiler_id'];
				// $this->fillOptionConditions['compiler_id'] = array($condition);
				// $condition['value'] = $row['build_target_id'];
				// $this->fillOptionConditions['build_target_id'] = array($condition);
				$condition['value'] = $row['testcase_priority_id'];
				$this->fillOptionConditions['testcase_priority_id'] = array($condition);
				$condition['value'] = $row['codec_stream_type_id'];
				$this->fillOptionConditions['codec_stream_type_id'] = array($condition);
				$condition['value'] = $row['codec_stream_format_id'];
				$this->fillOptionConditions['codec_stream_format_id'] = array($condition);
				$condition['value'] = $row['tester_id'];
				$this->fillOptionConditions['tester_id'] = array($condition);
				$condition['value'] = $row['test_env_id'];
				$this->fillOptionConditions['test_env_id'] = array($condition);
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
}

?>