<?php

require_once('jqgridmodel.php');

class xt_zzvw_cycle_detail_step extends jqGridModel{
    public function init($controller, array $options = null){
        $userAdmin = new Application_Model_Useradmin($this);
        $blankItem = true;
        $active = true;
        $userList = $userAdmin->getUserList();
        $activeUserList = $userAdmin->getUserList(false, $active);
        $searchUserList = $userAdmin->getUserList($blankItem);
        
        $options['db'] = 'xt';
        $options['table'] = 'zzvw_cycle_detail_step';
		$options['real_table'] = 'cycle_detail_step';
        $options['columns'] = array(
            'id'=>array('view'=>true),
			'step_number'=>array('label'=>'Step Num', 'editable'=>false, 'view'=>true),
			'description'=>array('editable'=>false, 'view'=>true),
			'expected_result'=>array('label'=>'Expect Result', 'editable'=>false, 'view'=>true),
			//'cycle_detail_id'=>array('excluded'=>true, 'editable'=>false, 'formatter'=>'text', 'hidden'=>true, 'hidedlg'=>true),
			//'testcase_ver_id'=>array('editable'=>false, 'hidden'=>true, 'hidedlg'=>true, 'view'=>false),
			'auto_level_id'=>array('label'=>'Auto Level', 'editable'=>false, 'view'=>true),'finish_time'=>array('label'=>'Finish Time', 'editable'=>false, 'edittype'=>'date', 'excluded'=>true, 'view'=>true),
			'result_type_id'=>array('label'=>'Result', 'editable'=>true, 'type'=>'select', 'edittype'=>'select', 'excluded'=>true, 'view'=>true),
			'defect_ids'=>array('label'=>'CR', 'editable'=>true, 'edittype'=>'text', 'formatter'=>'text', 'excluded'=>true, 'view'=>true),
			'comment'=>array('editable'=>true, 'edittype'=>'textarea', 'excluded'=>true, 'view'=>true),
			'isactive'=>array('editable'=>false, 'view'=>true)
			//'*'
        );

        $options['ver'] = '1.0';
		$options['gridOptions']['search'] = false;
		$options['gridOptions']['label'] = 'cycle case step';
        parent::init($controller, $options);
    } 
//by zxy 20130801	
/*  public function getSql($params, $limited = false){
//print_r($params);    
        $sql = $this->tool->calcSql($params, $limited);
//print_r($sql);
        return $sql;
    }*/
    
    public function getList(){
        $this->config();
        $ret = array();
		$params = $this->controller->getRequest()->getParams();
// print_r($params);
// return;
		$cycle_detail_id = $params['parent'];
        $params = $this->tool->parseParams('getList');
		$res = $this->db->query("SELECT * FROM cycle_detail where id=".$cycle_detail_id);
		$cycle_detail = $res->fetch();
/*
        // save the rownum to cookie
        $rownum = $params['limit']['rows'];
        $cookie = array('type'=>'rowNum', 'name'=>$this->get('db').'_'.$this->get('table'), 'content'=>json_encode(array('rowNum'=>$rownum)));
        $this->userAdmin->saveCookie($cookie);
*/        
//        $sql = $this->getSql($params, false);
		$sql = "SELECT * FROM testcase_ver_step WHERE testcase_ver_id=".$cycle_detail['testcase_ver_id'];      
        $res = $this->db->query($sql);
        $ret['records'] = $res->rowCount();            

        $ret['page'] = $params['page'];
        $ret['pages'] = 1;
        if ($params['limit']['rows'] > 0)
            $ret['pages'] = ceil($ret['records'] / $params['limit']['rows']);

        $sqlLimit = $this->tool->getLimitSql($params['limit']);
        if (empty($this->options['gridOptions']['treeGrid']) && !empty($sqlLimit)){
            $sql .= ' LIMIT '.$sqlLimit;
            $res = $this->db->query($sql);
        }

        $rows = array();
        while($row = $res->fetch()){
            $row = $this->getMoreInfoForRow($row);
            $rows[] = $row;
        }
        $ret['rows'] = $rows;
        $ret['sql'] = $sql;
        return $ret;
    }

    public function getButtons(){
		$params = $this->tool->parseParams();
//print_r($params);
		if (!empty($params['parent'])){
			$res = $this->db->query("SELECT cycle_id FROM cycle_detail WHERE id=".$params['parent']);
			$cycle_id = $res->fetch();
			if(!empty($cycle_id['cycle_id'])){//empty or isset
				$res = $this->db->query("SELECT cycle_status_id, creater_id, tester_ids FROM cycle WHERE id=".$params['parent']);
				$cycle = $res->fetch();
			}
		}
		$buttons = parent::getButtons();	
		unset($buttons['add']);
		unset($buttons['activate']);	
		unset($buttons['inactivate']);			
		if (isset($cycle['cycle_status_id']) && $cycle['cycle_status_id'] == CYCLE_STATUS_ONGOING){
			//$buttons['removecase'] = array('caption'=>'Remove Cases', 'title'=>'Remove testcases');//
			//admin && cycle owner
			$isowner = false;
			$istester = false;
			$isadmin = $this->userAdmin->isAdmin($this->currentUser);
			if(isset($cycle['creater_id']) && $this->currentUser == $cycle['creater_id'])
				$isowner = true;
			if(isset($cycle['tester_ids'])){
				$testers = explode(',', $cycle['tester_ids']);
				foreach($testers as $tester){
					if($this->currentUser == $tester)
						$istester = true;
				}
			}
			if($isowner || $isadmin || $istester){
				$buttons['set_result'] = array('caption'=>'Set Result', 'title'=>'Set the test results');
			}
		}
		return $buttons;
    }

	public function set_result(){
		$params = $this->tool->parseParams();
		if(is_array($params['element']))
			$element = implode(",", $params['element']);
		$real_table = $this->get('real_table');
		$res = $this->db->query("SELECT * FROM $real_table WHERE id in ($element)");
		$rowData = '';
		while($info = $res->fetch()){
			$rowData = $info;
			$this->db->update('cycle_detail_step', array('result_type_id'=>$params['select_item'], 'finish_time'=>date('Y-m-d H:i:s')), "id=".$info['id']);
		}
		foreach($params['element'] as $v){
			if($v != $rowData['id']){	
				$affectID = $this->db->insert('cycle_detail_step', array('result_type_id'=>$params['select_item'], 'cycle_detail_id'=>$params['cycle_detail_id'], 'testcase_ver_step_id'=>$v, 'finish_time'=>date('Y-m-d H:i:s')));			
			}
		}
	}
	
/*	protected function getInformationViewParams($params){
		$this->config();
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getInformationViewParams($params);
		// 补充管testcase的一些信息
		$testcase_fields = array('code', 'summary', 'testcase_module_id', 'testcase_testpoint_id', 'testcase_category_id', 'testcase_source_id', 'testcase_type_id');
		foreach($view_params['tabs']['view_edit']['colModels'] as $k=>$v){
			if (in_array($v['name'], $testcase_fields))
				$view_params['tabs']['view_edit']['caseModels'][$k] = $v;
			else
				$view_params['tabs']['view_edit']['verModels'][$k] = $v;
//			unset($view_params['tabs']['view_edit']['colModels'][$k]);
		}
		$view_params['tabs']['view_edit']['dir'] = 'xt/zzvw_testcase_ver';
		
		$testcase_ver = array();
		$edit_history = array();
		if (!empty($params['element'])){
			$res = $this->db->query("SELECT * FROM zzvw_testcase_ver where id={$params['element']}");
			$testcase_ver = $res->fetch();
			$vers = array('Current Version'=>$testcase_ver);
			if ($testcase_ver['update_from'] != 0){
				$res = $this->db->query("SELECT * FROM zzvw_testcase_ver WHERE testcase_id={$testcase_ver['testcase_id']} AND ver={$testcase_ver['update_from']}");
				$vers['base_ver'] = $res->fetch();
			}
			$res = $this->db->query("SELECT * FROM zzvw_testcase_ver WHERE testcase_id={$testcase_ver['testcase_id']} ORDER BY ver");
			while($row = $res->fetch()){
				if ($row['id'] == $params['element'])
					$row['ver'] .= ' (Current)';
				$edit_history[] = $row;
			}
			//如果currentUser是case的Owner并且Case处于Editing状态，则显示Ask2Review页面，该页面列出Reviewer的列表
			if ($testcase_ver['edit_status_id'] == EDIT_STATUS_EDITING && $testcase_ver['owner_id'] == $this->currentUser){
				$reviewer = $this->userAdmin->getReviewerList('testcase');
				$view_params['tabs']['askreview'] = array('reviewers'=>$reviewer, 'dir'=>'xt/zzvw_testcase_ver', 'label'=>'Ask to Review', 'vers'=>$vers);
			}
			//如果case处于Review_waiting或Reviewing状态，并且currentUser是该Case的一个Reviewer，则显示Review页面
			//在review页面，应显示基础版本和现版本的对比图，看哪些字段修改了
			if (($testcase_ver['edit_status_id'] == EDIT_STATUS_REVIEWING || $testcase_ver['edit_status_id'] == EDIT_STATUS_REVIEW_WAITING) && 
				$task_id = $this->userAdmin->isReviewer($this->currentUser, $this->get('db'), $this->get('table'), $params['element'])){
				$view_params['tabs']['ver_review'] = array('label'=>'Review', 'dir'=>'xt/zzvw_testcase_ver');
				$view_params['tabs']['ver_review']['vers'] = $vers;
				$view_params['tabs']['ver_review']['task_id'] = $task_id;
			}
		
			$view_params['tabs']['edit_history'] = array('label'=>'Edit History', 'db'=>$db, 'table'=>$table, 'id'=>$params['element'], 'disabled'=>empty($params['element']),
				'dir'=>'xt/zzvw_testcase_ver', 'testcase_ver'=>$testcase_ver, 'edit_history'=>$edit_history);
			
			// get the test history
			$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE testcase_id=".$testcase_ver['testcase_id']);
			$test_history = $res->fetchAll();
			$view_params['tabs']['test_history'] = array('label'=>'Test History', 'db'=>$db, 'table'=>$table, 'id'=>$params['element'], 'disabled'=>empty($params['element']),
				'dir'=>'xt/zzvw_testcase_ver', 'testcase_ver'=>$testcase_ver, 'test_history'=>$test_history);
		}
		
//print_r($view_params);		
		return $view_params;
	}*/
//by zxy 20130801    
    protected function _saveOne($db, $table, $pair){
//		$res = $this->db->query("select * from cycle_detail where id=".$pair['parent']);
//		$row = $res->fetch();
		$pair['cycle_detail_id'] = $pair['parent'];
//		$pair['testcase_ver_step_id'] = $pair['step_id'];
		$pair['testcase_ver_step_id'] = $pair['id'];
		$real_table = $this->get('real_table');
		$res = $this->db->query("SELECT * FROM $real_table WHERE testcase_ver_step_id={$pair['id']} AND cycle_detail_id=".$pair['cycle_detail_id']);
		$info = $res->fetch();
		if(empty($info))
			$pair['id'] = '';
		$finish_time = date('Y-m-d H:i:s');
		$pair['finish_time'] = $finish_time;
print_r($pair);
		return parent::_saveOne($db, $table, $pair);
    }
	
	protected function getViewEditButtons($params){
		// check if current user is the owner or admin
		$cycle = '';
		$btns = '';
// print_r($params);
		if($params['parent']){
			$res = $this->db->query("SELECT * FROM cycle_detail WHERE id =".$params['parent']);
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
	
	protected function getMoreInfoForRow($row){
		$params = $this->tool->parseParams();
		$row['result_type_id'] = null;
		$row['defect_ids'] = null;
		$row['finish_time'] = null;//初值应该是0
		$row['comment'] = null;
		if($params['parent'] && $row['id']){
			$res = $this->db->query("SELECT * FROM cycle_detail_step WHERE testcase_ver_step_id=".$row['id']." AND cycle_detail_id=".$params['parent']);
			if($info = $res->fetch()){
				$row['result_type_id'] = $info['result_type_id'];
				$row['defect_ids'] = $info['defect_ids'];
				$row['finish_time'] = $info['finish_time'];//初值应该是0
				$row['comment'] = $info['comment'];
			}
		}
		return $row;
    }
}