<?php

require_once('jqgridmodel.php');
require_once('kf_editstatus.php');

class xt_zzvw_cycle_detail extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'xt';
        $options['table'] = 'zzvw_cycle_detail';
        $options['columns'] = array(
			'id',
			'code'=>array('label'=>'Testcase', 'editable'=>false, 'query'=>true, 'unique'=>false),
			'summary'=>array('label'=>'Summary', 'editable'=>false),
			'testcase_module_id'=>array('label'=>'Module','query'=>true, 'editable'=>false, 'hidden'=>true),
			'testcase_testpoint_id'=>array('label'=>'Testpoint','query'=>true, 'editable'=>false, 'hidden'=>true),
			'testcase_id'=>array('editable'=>true, 'formatter'=>'text', 'hidden'=>true),
			'ver'=>array('label'=>'Ver', 'editable'=>false),
			'test_env_id'=>array('label'=>'Test Env'),
			'build_result_id'=>array('label'=>'Build Result'),
			'testcase_priority_id'=>array('label'=>'Priority', 'query'=>true, 'editable'=>false, 'hidden'=>true),

			'result_type_id'=>array('label'=>'Result', 'query'=>true), //'formatter'=>'resultLink'),
			'finish_time'=>array('label'=>'Finish Time', 'editable'=>false),
			'duration_minutes'=>array('hidden'=>true),
			'deadline'=>array('hidden'=>true, 'required'=>false),
			'tester_id'=>array('label'=>'Testor', 'query'=>true), //'formatter'=>'testorLink'),
			'auto_level_id'=>array('label'=>'Auto Level', 'query'=>true, 'editable'=>false),
			'defect_ids'=>array('label'=>'CR', 'query'=>true, 'formatter'=>'text'),
			'test_env_id'=>array('label'=>'Test Env'),
			'codec_stream_id'=>array('label'=>'Codec Stream'),
			'cycle_id'=>array('hidden'=>true),
			//'*'=>array('hidden'=>true, 'editable'=>false, 'view'=>false),
			'comment'
			//'objective'
		);
//        $options['gridOptions']['subGrid'] = true;	
		$options['gridOptions']['label'] = 'cycle cases';
        $options['ver'] = '1.0';
        parent::init($controller, $options);
    } 
	
	public function getButtons(){
		$params = $this->tool->parseParams();
		$cycle = array();
		if (!empty($params['parent'])){
			$res = $this->db->query("SELECT * FROM cycle WHERE id=".$params['parent']);
			$cycle = $res->fetch();
		}
		$buttons = parent::getButtons();	
		unset($buttons['add']);		
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
			//admin && cycle owner
			$isOwner = false;
			$isTester = false;
			$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
			if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
				$isOwner = true;
			if(isset($cycle['tester_ids'])){
				$testers = explode(',', $cycle['tester_ids']);
				foreach($testers as $tester){
					if($this->currentUser == $tester)
						$isTester = true;
				}
			}
			if($isOwner || $isAdmin){
				$buttons['removecase'] = array('caption'=>'Remove Cases', 'title'=>'Remove testcases');
				$buttons['set_tester'] = array('caption'=>'Assign Tester', 'title'=>'Set the tester for the cases');
			}
			if($isOwner || $isAdmin || $isTester){
				$buttons['set_build_result'] = array('caption'=>'Set Build Result', 'title'=>'Set the Build results');
				$buttons['set_result'] = array('caption'=>'Set Result', 'title'=>'Set the test results');
				$buttons['case_env'] = array('caption'=>'Environment', 'title'=>'Add Resource');
				$buttons['case_res'] = array('caption'=>'Codec Stream', 'title'=>'Add Codec Stream');
			}
		}
		$buttons['script'] = array('caption'=>'Generate Script', 'title'=>'Generate kinds of scripts');
		return $buttons;
	}
	
	public function set_result(){
		$params = $this->tool->parseParams();
		// cycle_detail_id(element,是唯一的) and result_type_id
		$this->db->update('cycle_detail', array('result_type_id'=>$params['select_item'], 'finish_time'=>date("Y-m-d H:i:s")), "id in (".implode(',', $params['element']).")");
		$res = $this->db->query("SELECT cycle_detail.id as id, cycle_detail.testcase_id as testcase_id, cycle.prj_id as prj_id, cycle.rel_id as rel_id FROM cycle_detail LEFT join cycle on cycle_id=cycle.id WHERE cycle_detail.id in (".implode(',', $params['element']).")");
		while($row = $res->fetch()){
				$tcres = $this->db->query("SELECT testcase_id FROM testcase_last_result WHERE testcase_id=".$row['testcase_id']." AND prj_id=".$row['prj_id']." AND rel_id=".$row['rel_id']);
				if($data = $tcres->fetch())
					$this->db->update('testcase_last_result', array('result_type_id'=>$params['select_item'], 'cycle_detail_id'=>$row['id'], 'tested'=>date("Y-m-d H:i:s")), "testcase_id=".$row['testcase_id']);
				else
					$this->db->insert('testcase_last_result', array('testcase_id'=>$row['testcase_id'], 'cycle_detail_id'=>$row['id'], 'result_type_id'=>$params['select_item'], 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'tested'=>date("Y-m-d H:i:s")));
				$this->db->update('testcase', array('last_run'=>date("Y-m-d H:i:s")), "id=".$row['testcase_id']);	
		}
		
		$res = $this->db->query("SELECT cycle_id FROM cycle_detail WHERE id=".$params['element'][0]);
		$cycle_id = $res->fetch();
		if($params['select_item'] == RESULT_TYPE_PASS){
			$this->updateForCycleCases($cycle_id['cycle_id']);
		}
		else if($params['select_item'] == RESULT_TYPE_FAIL){
			$this->updateForCycleCases($cycle_id['cycle_id']);
		}
		
	}
	
	public function set_tester(){
		$params = $this->tool->parseParams();
		// element and result_type_id
		$this->db->update('cycle_detail', array('tester_id'=>$params['select_item']), "id in (".implode(',', $params['element']).")");
		return 'success';
	}
	
	public function removecases(){
		$params = $this->tool->parseParams();
		$cycle = '';
		$has_result = array();
		$no_result = array();
		if (!empty($params['element'])){	
			$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".implode(",", $params['element']).")");
			while($info = $res->fetch()){
				if(empty($cycle) && !empty($info['cycle_id'])){
					$cycle_res = $this->db->query("SELECT id, creater_id FROM cycle WHERE id=".$info['cycle_id']);
					$cycle = $cycle_res->fetch();
				}
				if(!empty($info['result_type_id']) && $info['result_type_id']){
						$has_result[] = $info['id'];
				}
				else{
					$no_result[] = $info['id'];
				}
			}
		}
		//cycle的owner admin才可以删除case
		$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
		if(($cycle['creater_id'] && $this->currentUser == $cycle['creater_id']) || $isAdmin){ 
			if(!$params['flag']){
				if($no_result){
					//删除detail_step, 用到cycle_detail_id，删除与cycle_detail_id相关的所有detail_step
					$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $no_result).")");
					$this->db->delete('cycle_detail', "id in (".implode(',', $no_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以去掉的
				}
				if($has_result){
					$res = $this->db->query("SELECT code FROM zzvw_cycle_detail WHERE id in (".implode(",", $has_result).")");
					$code = $res->fetchAll();
					return json_encode($code);
				}
			}
			else{
				//删detail_step
				$this->db->delete('cycle_detail_step', "cycle_detail_id in (".implode(',', $has_result).")");
				//删除有结果的
				$this->db->delete('cycle_detail', "id in (".implode(',', $has_result).") AND cycle_id = {$cycle['id']}");//$cycle_id可以不加的
			}
		}
	}
	
	public function addcase(){
		$params = $this->tool->parseParams();
print_r($params);
		$ver_ids = array();
		$res = $this->db->query("SELECT test_env_id FROM cycle WHERE id=".$params['cycle_id']);
		$info = $res->fetch();//env
		foreach($params['ver_ids'] as $k=>$v){
			if($v != 'undefined'){
				$ver_id = $v;
				$testcase_id = $params['element'][$k];
				$detail_res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE cycle_id=".$params['cycle_id']." AND testcase_id=".$testcase_id);
				if($detail_row = $detail_res->fetch()){
					if ($detail_row['testcase_ver_id'] != $ver_id){
						if ($detail_row['result_type_id'] != 0){
							$this->db->update('cycle_detail', array('testcase_ver_id'=>$ver_id), "id=".$detail_row['id']);
							if ($params['replaced']){
								$this->db->update('cycle_detail', array('testcase_ver_id'=>$ver_id, 'result_type_id'=>0, 'build_result_type_id'=>0, 'finish_time'=>0), "id=".$detail_row['id']);
								$this->updateForCycleCases($params['cycle_id']);
							}
						}
					}
				}
				else{
					$res = $this->db->query("SELECT test_env_id FROM cycle WHERE id=".$params['cycle_id']);
					if($info = $res->fetch()){
						$this->db->insert('cycle_detail', array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$ver_id, 'testcase_id'=>$testcase_id, 'result_type_id'=>0, 'test_env_id'=>$info['test_env_id'], 'finish_time'=>0));
						$this->updateForCycleCases($params['cycle_id']);
					}
				}
			}
		}
	}
//认真修改	
	public function newaddcase(){
		$params = $this->tool->parseParams();
		$res = $this->db->query("SELECT prj_id FROM cycle WHERE id=".$params['cycle_id']);
		$prj = $res->fetch();
		$res = $this->db->query('SELECT * FROM zzvw_cycle_detail WHERE id in ('.implode(',', $params['element']).')');
		//判断是否有该条记录
		while($detail = $res->fetch()){
print_r(1);
			$vers_res = $this->db->query("SELECT * FROM prj_testcase_ver WHERE testcase_id=".$detail['testcase_id']." AND prj_id=".$prj['prj_id']." AND edit_status_id in (".EDIT_STATUS_PUBLISHED." ,".EDIT_STATUS_GOLDEN.")");
			//判断是否有该ver
			if(!isset($vers[$detail['testcase_id']][$detail['prj_id']]))
				$vers[$detail['testcase_id']][$detail['prj_id']] = $vers_res->fetch();
			$vers = $vers[$detail['testcase_id']][$detail['prj_id']];
			if ($vers){
print_r(2);
				$sql = "SELECT * FROM cycle_detail WHERE cycle_id=".$params['cycle_id']." AND testcase_id=".$detail['testcase_id']." AND test_env_id=".$detail['test_env_id'];
				if(!empty($detail['codec_stream_id']))
					$sql .= " AND testcase_id=".$detail['codec_stream_id'];
				$detail_res = $this->db->query($sql);
				if ($detail_row = $detail_res->fetch()){
					if ($detail_row['testcase_ver_id'] != $vers['testcase_ver_id']){
						$this->db->update('cycle_detail', array('testcase_ver_id'=>$vers['testcase_ver_id']), "id=".$detail_row['id']);
						if ($detail_row['result_type_id'] != 0){
							if ($params['replaced']){
								$this->db->update('cycle_detail', array('testcase_ver_id'=>$vers['testcase_ver_id'], 'result_type_id'=>0, 'build_result_type_id'=>0, 'finish_time'=>0), "id=".$detail_row['id']);
								$this->updateForCycleCases($params['cycle_id']);
							}
						}
					}
				}
				// else if($env){
				else{
						$this->db->insert('cycle_detail', array('cycle_id'=>$params['cycle_id'], 'testcase_ver_id'=>$vers['testcase_ver_id'], 'testcase_id'=>$vers['testcase_id'], 'result_type_id'=>0, 'test_env_id'=>$detail['test_env_id'], 'codec_stream_id'=>$detail['codec_stream_id'], 'finish_time'=>0));
						$this->updateForCycleCases($params['cycle_id']);
				}
			}
		}		
	}
	
	protected function _saveOne($db, $table, $pair){
		$affectID = parent::_saveOne($db, 'cycle_detail', $pair);
		$res = $this->db->query("SELECT cycle_id as cycle FROM cycle_detail WHERE id = {$pair['id']} and testcase_id = {$pair['testcase_id']}");
		$info = $res->fetch();
		if($pair['result_type_id'] == RESULT_TYPE_PASS){
			$this->updateForCycleCases($info['cycle']);
		}
		else if($pair['result_type_id'] == RESULT_TYPE_FAIL){
			$this->updateForCycleCases($info['cycle']);
		}
		return $affectID;
	}
	
/*	
	public function script(){
		
	}
*/	
/*	
    protected function getMoreInfoForRow($row){
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']}");
		$info = $res->fetch();
		$row['total_cases'] = $info['cases'];
		
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_PASS);
		$info = $res->fetch();
		$row['pass_cases'] = $info['cases'];
		
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$row['id']} and result_type_id=".RESULT_TYPE_FAIL);
		$info = $res->fetch();
		$row['fail_cases'] = $info['cases'];
		return $row;
	}
*/	
	protected function getInformationViewParams($params){
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getInformationViewParams($params);
		$view_params['tabs']['view_edit']['dir'] = 'xt/zzvw_cycle_detail';
		
//		$sql = "SELECT * FROM zzvw_cycle_detail WHERE cycle_id=".$view_params['id'];
//		$res = $this->db->query($sql);
//		$view_params['tabs']['detail'] = array('dir'=>'xt/zzvw_cycle', 'label'=>'Test Cases', 'db'=>$db, 'table'=>$table, 'cycle_id'=>$params['element']);//, 'detail'=>$res->fetchAll());

//		$view_params['tabs']['reports'] = array('label'=>'Reports', 'cycle_id'=>$params['element']);
		return $view_params;
	}
/*	
	private function saveDetail($detail){
		$detail_id = $this->_saveOne('xt', 'cycle_detail', $detail);
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE id=$detail_id");
		$row = $res->fetch();
		$last_result = array('testcase_id'=>$row['testcase_id'], 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id']);
		$last_id = $this->tool->rowExist($last_result, 'testcase_last_result', 'xt');
		if (!empty($detail['result_type_id']) && ($detail['result_type_id'] == RESULT_TYPE_PASS || $detail['result_type_id'] == RESULT_TYPE_FAIL)){
			$last_result['cycle_detail_id'] = $detail_id;
			$last_result['id'] = $last_id;
			$this->_saveOne('xt', 'testcase_last_result', $last_result);
		}
		else{
			if ($last_id){
				// 需要将前一个结果置入
				$res = $this->db->query("SELECT id FROM zzvw_cycle_detail WHERE prj_id={$row['prj_id']} AND rel_id={$row['rel_id']} AND (result_type_id=".RESULT_TYPE_PASS." OR result_type_id=".RESULT_TYPE_FAIL.")");
				if ($row = $res->fetch())
					$this->db->update('xt.testcase_last_result', array('cycle_detail_id'=>$row['id']), "id=$last_id");
				else
					$this->db->del('xt.testcase_last_result', "id=$last_id");
			}
		}
	}
*/	
	public function updateForCycleCases($cycle_id){
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id}");
		$info = $res->fetch();
		$total_cases = $info['cases'];
		
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id} and result_type_id=".RESULT_TYPE_PASS);
		$info = $res->fetch();
		$pass_cases = $info['cases'];
		
		$pass_rate = 0;
		$color = 'red';
		if ($total_cases > 0){
			$pass_rate = number_format($pass_cases/$total_cases * 100, 2);
			if ($pass_rate >= 85)
				$color = 'blue';
			else if ($pass_rate >= 60)
				$color = 'gray';
		}
		$pass_and_rate = sprintf("<span style='color:$color'>%-6d [%5.2f%%]</span>", $pass_cases, $pass_rate);
		
		$res = $this->db->query("SELECT COUNT(*) as cases FROM cycle_detail WHERE cycle_id={$cycle_id} and result_type_id=".RESULT_TYPE_FAIL);
		$info = $res->fetch();
		$fail_cases = $info['cases'];
		
		$this->db->update('cycle', array('total_cases'=>$total_cases, 'pass_and_rate'=>$pass_and_rate, 'pass_cases'=>$pass_cases, 'pass_rate'=>$pass_rate, 'fail_cases'=>$fail_cases), "id = {$cycle_id}");
	}
	
	public function getButtonFlag(){
		$params = $this->tool->parseParams();
		if (!empty($params['hidden'])){
			$hidden = json_decode($params['hidden']);
			foreach($hidden as $k=>$v){
				if($k='cycle_id' && !empty($v)){
					$res = $this->db->query("SELECT * FROM cycle WHERE id=$v");
					$cycle = $res->fetch();
					if (isset($cycle['cycle_status_id'])){ 
						if($cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
							//admin && cycle owner
							$isOwner = false;
							$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
							//$isOwner = $this->isOwner('cycle', $params['parent']);//该函数有点问题？？？？
							if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
								$isOwner = true;
							if(!$isOwner && !$isAdmin)
								return false;
						}
						else if($cycle['cycle_status_id'] == CYCLE_STATUS_FROZEN){
							return  false;
						}
					}
				}
			}
		}
		return true;
	}
	
	public function resultInfo(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            //添加view
			if (!empty($params['element'])){		
				$sql = "SELECT cycle_id FROM cycle_detail WHERE id=".$params['element'];
				$res = $this->db->query($sql);
				$detail = $res->fetch();
				if($detail){
					$sql = "SELECT name FROM cycle WHERE id=".$detail['cycle_id'];
					$res = $this->db->query($sql);
					$cycle = $res->fetch();
					$res = $this->db->query("SELECT testcase_id, issue_comment FROM cycle_detail WHERE id=".$params['element']);
					$cycle_detai = $res->fetch();
					$res = $this->db->query("SELECT code FROM testcase WHERE id=".$cycle_detai['testcase_id']);
					$code = $res->fetch();			
					$res = $this->db->query("SELECT id, name FROM result_type");
					while($result = $res->fetch())
						$results[$result['id']] = $result['name'];				
					$res = $this->db->query("SELECT id, name FROM test_env");
					while($env = $res->fetch())
						$envs[$env['id']] = $env['name'];					
					$cols = array(
						array('name'=>'cycle_id', 'label'=>'Test Cycle', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$cycle['name']),
						array('name'=>'testcase_code', 'label'=>'Test Case', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$code['code']),
						array('name'=>'test_env', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$envs)), //'editrules'=>array('required'=>true)),
						array('name'=>'result_type', 'label'=>'result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$params['result_type_id'], 'editoptions'=>array('value'=>$results), 'editrules'=>array('required'=>true)),
						array('name'=>'cr_comments', 'label'=>'CR Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
						array('name'=>'cr_numbers', 'label'=>'CR', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
						array('name'=>'logfile', 'label'=>'logfile', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file'),
						array('name'=>'issue_comment', 'label'=>'Issue Comment', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'textarea', 'defval'=>$cycle_detai['issue_comment']),
						array('name'=>'new_issue_comment', 'label'=>'New Issue Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
						array('name'=>'submit_a_cr', 'label'=>'Submit A CR', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'submit'))),
						array('name'=>'cq_password', 'label'=>'CQ Password', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'password'),
						array('name'=>'cr_headline', 'label'=>'CR Headline', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
						array('name'=>'cr_description', 'label'=>'CR Description', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'textarea')
					);
					$this->renderView('new_element.phtml', array('cols'=>$cols), '/jqgrid');
				}
			}	                                    
		}
		else{
			
		}
	}
	
	public function saveOneResult(){
		$date = date('Y-m-d H:i:s');
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
//print_r($params);
			$data = array();
			$data['test_env_id'] = $params['test_env'];
			$data['result_type_id'] = $params['result_type'];
			$data['finish_time'] = date("Y-m-d H:i:s");
			if(!empty($params['cr_comments']))
				$data['comment'] = $params['cr_comments'];
//print_r($data);
			$this->db->update('cycle_detail', $data, "id=".$params['element']);
			$res = $this->db->query("SELECT cycle_detail.id as id, cycle_detail.testcase_id as testcase_id, cycle.prj_id as prj_id, cycle.rel_id as rel_id FROM cycle_detail LEFT join cycle on cycle_id=cycle.id WHERE cycle_detail.id=".$params['element']);
			if($row = $res->fetch()){
				$tcres = $this->db->query("SELECT testcase_id FROM testcase_last_result WHERE testcase_id=".$row['testcase_id']." AND prj_id=".$row['prj_id']." AND rel_id=".$row['rel_id']);
				if($data = $tcres->fetch())
					$this->db->update('testcase_last_result', array('result_type_id'=>$params['result_type'], 'cycle_detail_id'=>$row['id'], 'tested'=>date("Y-m-d H:i:s")), "testcase_id=".$row['testcase_id']);
				else
					$this->db->insert('testcase_last_result', array('testcase_id'=>$row['testcase_id'], 'cycle_detail_id'=>$row['id'], 'result_type_id'=>$params['result_type'], 'prj_id'=>$row['prj_id'], 'rel_id'=>$row['rel_id'], 'tested'=>date("Y-m-d H:i:s")));
				$this->db->update('testcase', array('last_run'=>date("Y-m-d H:i:s")), "id=".$row['testcase_id']);	
			}
			if(!empty($params['new_issue_comment'])){
				$author = $this->currentUserName;
				$sql = "UPDATE cycle_detail SET issue_comment=concat('\\n', '".$author.
				":".$date.
				"', ".$params['new_issue_comment'].
				") WHERE id=".$params['element'];
//print_r($sql);
				$res = $this->db->query($sql);
			}
			//logfile，存放在php端的logfile文件夹中
			//$filename = '';
			
			//发送至CQ的处理，waiting
			/*if(!empty($params['submit_a_cr'])){
					
				}
			}*/
		}
		else{
				
		}
	}
	
	public function getGridOptions(){
		$params = $this->tool->parseParams();
		parent::getGridOptions();		
		//可以考虑将buttons的权限也放在这个地方，可以考虑一下
		if (!empty($params['parent'])){
			$res = $this->db->query("SELECT * FROM cycle WHERE id=".$params['parent']);
			$cycle = $res->fetch();
			$isOwner = false;
			$isTester = false;
			$isAdmin = false;
			//$isOwner = $this->isOwner('cycle', $params['parent']);//该函数有点问题？？？？
			if($cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
				$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
				if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
					$isOwner = true;
				if(isset($cycle['tester_ids'])){
					$testers = explode(',', $cycle['tester_ids']);
					foreach($testers as $tester){
						if($this->currentUser == $tester)
							$isTester = true;
					}
				}
			}
			foreach($this->options['gridOptions']['colModel'] as $k=>&$m){
				if ($m['name'] == 'result_type_id'){
					if($m['formatter'] == 'select'){
						if($isOwner || $isAdmin || $isTester)
							$this->options['gridOptions']['colModel'][$k]['formatter'] = 'resultLink';
							//不适用于edittype,因为只有formatter才拥有自定义的编辑类型，edittype的权限问题？？？？
							//$this->options['gridOptions']['colModel'][$k]['edittype'] = 'resultLink';
					}
				}
				if ($m['name'] == 'build_result_id'){
					if($m['formatter'] == 'select'){
						if($isOwner || $isAdmin || $isTester)
							$this->options['gridOptions']['colModel'][$k]['formatter'] = 'bResultLink';
					}
				}
				if ($m['name'] == 'tester_id'){
					if($m['formatter'] == 'select'){
						if($isOwner || $isAdmin)
							$this->options['gridOptions']['colModel'][$k]['formatter'] = 'testorLink';
							//button权限放在这里？
							//$this->options['gridOptions']['colModel'][$k]['edittype'] = 'testorLink';
					}
				}
			}
		}
        return $this->options;
    }


	
	public function case_env(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            //clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
			if (!empty($params['test_env_id'])){
				if(is_array($params['element']))//其实判断条件可以去掉的
					$params['element'] = implode(",", $params['element']);
				if(is_array($params['test_env_id']))
					$test_env_id = implode(",", $params['test_env_id']);
					
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".$params['element'].")");
				while($info = $res->fetch()){
					$test_env = $params['test_env_id'];
					if(empty($cycle_id))
						$cycle_id = $info['cycle_id'];
						
					$condition = " testcase_id={$info['testcase_id']} AND testcase_ver_id={$info['testcase_ver_id']}";
					if($info['codec_stream_id'])
						$condition .= " AND codec_stream_id={$info['codec_stream_id']}";
					$condition .= " AND cycle_id={$cycle_id} AND test_env_id in (".$test_env_id.")";
					$detail_res = $this->db->query("SELECT * FROM cycle_detail WHERE".$condition);
					while($detail = $detail_res->fetch()){
						foreach($test_env as $k=>$val){
							if($val == $detail['test_env_id'])
								unset($test_env[$k]);
						}
					}
					if(!empty($test_env)){
						if(empty($info['test_env_id'])){
							$this->db->update('cycle_detail', array('test_env_id'=>$test_env[0]), "id = ".$info['id']);
							unset($test_env[0]);
							foreach($test_env as $val)
								$this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'result_type_id'=>0, 'codec_stream_id'=>$info['codec_stream_id'], 'test_env_id'=>$val, 'finish_time'=>0));
						}
						else{
							foreach($test_env as $val){
								if($val != $info['test_env_id'] )
									$this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'result_type_id'=>0, 'codec_stream_id'=>$info['codec_stream_id'], 'test_env_id'=>$val, 'finish_time'=>0));
							}
						}
					}
				}
			}                                    
		}
		else{	
		}
	}
	
	protected function getViewEditButtons($params){
		// check if current user is the owner or admin
		$cycle = '';
		$btns = '';
		if(!empty($params['element'])){
			$res = $this->db->query("SELECT * FROM cycle_detail WHERE id =".$params['element']);
			if($cycle_detail = $res->fetch()){
				$res = $this->db->query("SELECT * FROM cycle WHERE id=".$cycle_detail['cycle_id']);
				$cycle = $res->fetch();
			}
		}
		
		$style = 'position:relative;float:right';
		$display = $style;
		$hide = $style.';display:none';	
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
			$isTester = false;
			$isOwner = $this->isOwner('cycle', $params);//传的参数不是很合理
			$isAdmin = $this->userAdmin->isAdmin($this->currentUser);
			if(isset($cycle['tester_ids'])){
				$testers = explode(',', $cycle['tester_ids']);
				foreach($testers as $tester){
					if($this->currentUser == $tester)
						$isTester = true;
				}
			}
			if($isOwner || $isAdmin || $isTester){
				$btns = parent::getViewEditButtons($params);
				unset($btns['cloneit']);
			}
		}
		return $btns;
	}
	
	/*public function case_res1(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            if(!empty($params['element']) && !empty($params['resource_id'])){
				$cycle_id = '';
				if(is_array($params['element']))
					$parmas['element'] = implode(",", $parmas['element']);
				//if(is_array($params['resource_id']))
					//$resource_ids = implode(",", $parmas['resource_id']);
					
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".$parmas['element'].")");
				while($info = $res->fetch()){
					if(empty($cycle_id))
						$cycle_id = $info['cycle_id'];
					$this->db->update('cycle_detail', array('resource_id'=>$params['resource_id'][0]), "id = ".$info['id']);
					unset($params['resource_id'][0]);
					foreach($params['resource_id'] as $resouce){
						$affectID = $this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'resource_id'=>$resource));
					}
				}
			}
		}
		else{
		}
	}*/
	public function case_res(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            if(!empty($params['element']) && !empty($params['resource_id'])){
				$cycle_id = '';
				$test_env_id = '';
				$res_id = $params['resource_id'];
				if(is_array($params['element']))
					$params['element'] = implode(",", $params['element']);
				if(is_array($params['resource_id']))
					$params['resource_id'] = implode(",", $params['resource_id']);
					
				$res = $this->db->query("SELECT * FROM cycle_detail WHERE id in (".$params['element'].")");
				while($info = $res->fetch()){
					$resource = $res_id;
					$condition = " testcase_id={$info['testcase_id']} AND testcase_ver_id={$info['testcase_ver_id']}";
					if(empty($cycle_id)){
						$cycle_id = $info['cycle_id'];
					}
					
					if(!empty($cycle_id)){//一定是有的
						if(!empty($info['test_env_id']))
							$condition .= " AND test_env_id={$info['test_env_id']}";
						$condition .= " AND cycle_id={$cycle_id} AND codec_stream_id in (".$params['resource_id'].")";
						$detail_res = $this->db->query("SELECT * FROM cycle_detail WHERE".$condition);
						while($detail = $detail_res->fetch()){
							foreach($resource as $k=>$val){
								if($val == $detail['codec_stream_id'])
									unset($resource[$k]);
							}
						}
						if($resource){
							if(empty($info['codec_stream_id'])){
								$this->db->update('cycle_detail', array('codec_stream_id'=>$resource[0]), "id = ".$info['id']);
								unset($resource[0]);
								foreach($resource as $val)
									$this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'result_type_id'=>0, 'codec_stream_id'=>$val, 'test_env_id'=>$info['test_env_id'], 'finish_time'=>0));
								
							}
							else{
								foreach($resource as $val){
									if($val != $info['codec_stream_id'])
										$this->db->insert('cycle_detail', array('cycle_id'=>$cycle_id,'testcase_id'=>$info['testcase_id'], 'testcase_ver_id'=>$info['testcase_ver_id'], 'result_type_id'=>0, 'codec_stream_id'=>$val, 'test_env_id'=>$info['test_env_id'], 'finish_time'=>0));
								}
							}
						}
					}
				}
			}
		}
		else{
		}
	}
	
	public function getModule(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT testcase_module.id as id, testcase_module.name as name FROM cycle_detail LEFT JOIN testcase ON cycle_detail.testcase_id=testcase.id LEFT JOIN testcase_module ON testcase.testcase_module_id=testcase_module.id";
		$where = "1";//对么？？？
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function getPriority(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT testcase_priority.id as id, testcase_priority.name as name FROM cycle_detail LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id LEFT JOIN testcase_priority ON testcase_ver.testcase_priority_id=testcase_priority.id";
		$where = "1";//对么？？？
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function getAutoLevel(){
		$params = $this->tool->parseParams();
		$sql = "SELECT DISTINCT auto_level.id as id, auto_level.name as name FROM cycle_detail LEFT JOIN testcase_ver ON cycle_detail.testcase_ver_id=testcase_ver.id LEFT JOIN auto_level ON testcase_ver.auto_level_id=auto_level.id";
		$where = "1";//对么？？？
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND name is not null";
		$sql .= " WHERE $where ORDER BY name ASC";
		$res = $this->db->query($sql);
		return json_encode($res->fetchAll());
	}
	
	public function getTester(){
		$params = $this->tool->parseParams();
		if($params['condition']){
			$sql = "SELECT tester_ids FROM cycle WHERE id=".$params['condition'];
			$res = $this->db->query($sql);
			$cycle = $res->fetch();
			$tester = explode(",", $cycle['tester_ids']);
			foreach($tester as $key=>$val){
				if(empty($val)){
					unset($tester[$key]);
				}
			}
			$cycle['tester_ids'] = implode(",", $tester);
			$sql = "SELECT id, nickname as name FROM users WHERE id in (".$cycle['tester_ids'].")";
			$res = $this->userAdmin->db->query($sql);
			$this->renderView('select_item.phtml', array('type'=>"tester", 'items'=>$res->fetchAll()), '/jqgrid');
		}
	}
	
	public function cycle_cases(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
                                          
		}
		else{
			$cols = array(
				//array('name'=>'id', 'label'=>'Test Cycle', 'query=>true', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'select'),
				array('name'=>'testcase_id', 'label'=>'Testcase', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
				array('name'=>'prj_id', 'label'=>'Prj', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_type_id', 'label'=>'Cycle Type', 'query'=>'true', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')), //'editrules'=>array('required'=>true)),
				//array('name'=>'myname', 'label'=>'result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'text'),
				array('name'=>'cycle_id', 'label'=>'Cycle', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_module_id', 'label'=>'Module', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_testpoint_id', 'label'=>'Test Point', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'testcase_priority_id', 'label'=>'Priority', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'auto_level_id', 'label'=>'Auto Level', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'result_type_id', 'label'=>'Result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'tester_id', 'label'=>'Tester', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
				array('name'=>'defect_ids', 'label'=>'CR', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text','type'=>'text', 'searchoptions'=>array('value'=>''))
			);
			foreach($cols as $row=>$search){
				foreach($search as $key=>$val){
					if($key == 'name' && $val != 'defect_ids' && $val != 'testcase_id'){	
						if (preg_match('/^(.+)_(ids?)$/i', $val, $matches))
							$data = $this->getdata($matches[1]);
					}
					if(isset($data) && ($key == 'searchoptions')){
						foreach($val as $k=>$v)
							$cols[$row][$key][$k] = $data;
						unset($data);
					}
				}
			}
			
			$this->renderView('other_cycle_case.phtml', array('cols'=>$cols, 'container'=>$params['container'], 'db'=>'xt', 'table'=>'zzvw_cycle_detail', 'buttonFlag'=>false, 'advanced'=>''));	      
		}
	}
	private function getdata($table){
		if($table == 'tester'){
			$data = $this->userAdmin->getUserList(true);
		}else{
			$data['0'] = '';
			$sql = "SELECT id, name FROM $table";
			$res = $this->db->query($sql);
			while($info = $res->fetch()){
				$data[$info['id']] = $info['name'];
			}
		}
		return $data;
	}
	public function build_result(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){
            $res = $this->db->query("SELECT id, name FROM result_type");
		    while($info = $res->fetch()){
				$build_result[$info['id']] = $info['name'];
			}
			$cols = array(
				array('name'=>'build_result_id', 'label'=>'Build Result',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'deval'=>$params['build_result_id'], 'editoptions'=>array('value'=>$build_result)),
				);
			$this->renderView('build_result.phtml', array('cols'=>$cols));	                              
		}
		else{      
		}
	}
	
	public function set_build_result(){
		$params = $this->tool->parseParams();
		if(is_array($params['element']))
			$params['element'] = implode(',', $params['element']);
		$this->db->update('cycle_detail', array('build_result_id'=>$params['build_result_id']), "id in (".$params['element'].")");
	}
	
	public function script(){
		$params = $this->tool->parseParams();
		$ret = '';
        $rename = "cycle_detail".implode("_", $params['element']);
		$rename .= '_'.(($params['script_type'] == 1) ? 'Auto' : 'AutoMan');
		$realFileName = SCRIPT_ROOT.'/'.$rename.'_'.rand();
		$download = array("rename"=>$rename, "filename"=>$realFileName, "remove"=>1);
		$sql = "SELECT * FROM zzvw_cycle_detail WHERE id in (".implode(",", $params['element']).") AND auto_level_id=".$params['script_type'];
		$result = $this->db->query($sql);
		$str = '';
		while ($row = $result->fetch()){
			if(!empty($row["command"]))
				$str .= $row["testcase_id"] . " " . $row["command"] . "\n";
		}
		if ($str != ''){
			$handle = @fopen($realFileName, 'wb');
			if ($handle){
				if (fwrite($handle, $str)){
					fclose($handle);
					$ret = json_encode($download);
				}
			}
		}
        return $ret;
	}
	
	public function getlatestresult(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT code FROM testcase WHERE id=".$params['id']);
			$str = '';
			if($info = $res->fetch()){
				$str .= '<table style="width:400px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="4">'.$info['code'].' : 5 Test Result(s):</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:10%">Result</td>'.
					'<td style="width:20%">CRID</td><td style="width:50%">Comment</td>'.
					'<td style="width:50%">Cycle</td>';
				$sql = 'SELECT result_type.name as result_type, defect_ids, comment, cycle.name as cycle FROM cycle_detail'.
				' LEFT JOIN result_type ON result_type_id=result_type.id LEFT JOIN cycle ON cycle_id=cycle.id'. 
				' WHERE cycle_detail.testcase_id='.$params['id'].' AND cycle_detail.id NOT LIKE '.$params['id'].
				' ORDER BY finish_time DESC limit 0, 5';
				$res = $this->db->query($sql);
				$currentRow = 0;
				while($row = $res->fetch()){
					if($currentRow % 2)
						$class = 'odd';
					else
						$class = 'even';
					if(empty($row['result_type']))
						$row['result_type'] = 'null';
					if(empty($row['defect_ids']))
						$row['defect_ids'] = 'null';
					if(empty($row['comment']))
						$row['comment'] = 'null';
					if(empty($row['cycle']))
						$row['cycle'] = 'null';
					$str .= '<tr class="'.$class.'">';
					$str .= '<td>'.$row['result_type'].'</td>';
					$str .= '<td>'.$row['defect_ids'].'</td>';
					$str .= '<td>'.$row['comment'].'</td>';
					$str .= '<td>'.$row['cycle'].'</td>';
					$str .= '</tr>';
					$currentRow ++;
				}
				$str .= '</table>';
			}
			
			echo $str;
		}
		else{      
		}
	}
	
	public function getcrossresult(){
		$params = $this->tool->parseParams();
		if ($this->controller->getRequest()->isPost()){	
			$res = $this->db->query("SELECT code FROM testcase WHERE id=".$params['id']);
			$str = '';
			if($info = $res->fetch()){
				$str .= '<table style="width:400px" class="table">';
				$str .= '<tr class="tabletitle"><td colspan="4">'.$info['code'].' cross Project Test Result(s):</td></tr>';
				$str .= '<tr class="tablecaption"><td style="width:50%">Project</td>'.
					'<td style="width:50%">Release</td><td style="width:50%">Cycle</td>'.
					'<td style="width:10%">Result</td>';
				$sql = 'SELECT result_type.name as result_type, cycle.name as cycle, prj.name as prj, rel.name as rel FROM testcase_last_result lastresult'.
				' LEFT JOIN result_type ON result_type_id=result_type.id LEFT JOIN cycle_detail ON cycle_detail_id=cycle_detail.id LEFT JOIN prj ON lastresult.prj_id=prj.id'. 
				' LEFT JOIN rel ON lastresult.rel_id= rel.id LEFT JOIN cycle ON cycle_detail.cycle_id=cycle.id WHERE lastresult.testcase_id in ('.$params['id'].') limit 0, 5';
				$res = $this->db->query($sql);
				$currentRow = 0;
				while($row = $res->fetch()){
					if($currentRow % 2)
						$class = 'odd';
					else
						$class = 'even';
					if(empty($row['prj']))
						$row['prj'] = 'null';
					if(empty($row['result_type']))
						$row['result_type'] = 'null';
					if(empty($row['rel']))
						$row['rel'] = 'null';
					if(empty($row['cycle']))
						$row['cycle'] = 'null';
					$str .= '<tr class="'.$class.'">';
					$str .= '<td>'.$row['prj'].'</td>';
					$str .= '<td>'.$row['rel'].'</td>';
					$str .= '<td>'.$row['cycle'].'</td>';
					$str .= '<td>'.$row['result_type'].'</td>';
					$str .= '</tr>';
					$currentRow ++;
				}
				$str .= '</table>';
			}
			
			echo $str;
		}
		else{      
		}
	}
}
