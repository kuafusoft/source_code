<?php

require_once('jqgridmodel.php');
require_once('kf_editstatus.php');
require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/xt_testcase.php');
require_once('base_report.php');

class ver_report extends base_report{
	private $baseValue = array();
	private $baseTable = array();
    public function __construct($params = array()){
//		print_r($params);
		$sheetTitles = array('Testcase Information');
		if (!empty($params['edit_history']))
			$sheetTitles[1] = 'Edit History';
		if (!empty($params['test_history']))
			$sheetTitles[2] = 'Test History';
		parent::__construct($sheetTitles, $params);
		// fill in the base values
		$this->baseTable = array('testcase_type', 'testcase_source', 'testcase_category', 'testcase_source', 'testcase_priority', 'prj', 'auto_level', 'result_type', 
			'testcase_module', 'testcase_testpoint', 'rel');
		foreach($this->baseTable as $t){
			$res = $this->params['db']->query("SELECT id, name FROM $t");
			while($row = $res->fetch())
				$this->baseValue[$t][$row['id']] = $row['name'];
		}
			
		// set the columnheader
		$header = array(
			array('label'=>'Testcase ID', 'width'=>200, 'index'=>'code'),
			array('label'=>'Summary', 'width'=>200, 'index'=>'summary'),
			array('label'=>'Module', 'width'=>200, 'index'=>'testcase_module_id'),
			array('label'=>'Testpoint', 'width'=>200, 'index'=>'testcase_testpoint_id'),
			array('label'=>'Type', 'width'=>200, 'index'=>'testcase_type_id'),
			array('label'=>'Category', 'width'=>200, 'index'=>'testcase_category_id'),
			array('label'=>'Source', 'width'=>200, 'index'=>'testcase_source_id'),
			array('label'=>'Project', 'width'=>200, 'index'=>'prj_id'),
			array('label'=>'Version', 'width'=>200, 'index'=>'ver'),
			array('label'=>'Edit Status', 'width'=>200, 'index'=>'edit_status_id'),
			array('label'=>'Auto Level', 'width'=>200, 'index'=>'auto_level_id'),
			array('label'=>'Priority', 'width'=>200, 'index'=>'testcase_priority_id'),
			array('label'=>'Auto Run Seconds', 'width'=>200, 'index'=>'auto_run_seconds'),
			array('label'=>'Manual Run Seconds', 'width'=>200, 'index'=>'manual_run_seconds'),
			array('label'=>'Command', 'width'=>200, 'index'=>'command'),
			array('label'=>'Objective', 'width'=>200, 'index'=>'Objective'),
			array('label'=>'Precondition', 'width'=>200, 'index'=>'precondition'),
			array('label'=>'Steps', 'width'=>200, 'index'=>'steps'),
			array('label'=>'Expected Result', 'width'=>200, 'index'=>'expected_result'),
			array('label'=>'Create Time', 'width'=>200, 'index'=>'created'),
			array('label'=>'Last Update', 'width'=>200, 'index'=>'updated'),
			array('label'=>'Owner', 'width'=>200, 'index'=>'owner_id'),
			array('label'=>'Update Comment', 'width'=>200, 'index'=>'update_comment'),
			array('label'=>'Review Comment', 'width'=>200, 'index'=>'review_comment'),
			array('label'=>'Is Active', 'width'=>200, 'index'=>'isactive'),
		);
		$this->setColumnHeader(array('rows'=>array($header)), 0);
		$header = array(
			array('label'=>'Testcase ID', 'width'=>200, 'index'=>'code'),
			array('label'=>'Summary', 'width'=>200, 'index'=>'summary'),
			array('label'=>'Module', 'width'=>200, 'index'=>'testcase_module_id'),
			array('label'=>'Testpoint', 'width'=>200, 'index'=>'testcase_testpoint_id'),
			array('label'=>'Type', 'width'=>200, 'index'=>'testcase_type_id'),
			array('label'=>'Category', 'width'=>200, 'index'=>'testcase_category_id'),
			array('label'=>'Source', 'width'=>200, 'index'=>'testcase_source_id'),
			array('label'=>'Version', 'width'=>200, 'index'=>'ver'),
			array('label'=>'Edit Status', 'width'=>200, 'index'=>'edit_status_id'),
			array('label'=>'Auto Level', 'width'=>200, 'index'=>'auto_level_id'),
			array('label'=>'Priority', 'width'=>200, 'index'=>'testcase_priority_id'),
			array('label'=>'Auto Run Seconds', 'width'=>200, 'index'=>'auto_run_seconds'),
			array('label'=>'Manual Run Seconds', 'width'=>200, 'index'=>'manual_run_seconds'),
			array('label'=>'Command', 'width'=>200, 'index'=>'command'),
			array('label'=>'Objective', 'width'=>200, 'index'=>'Objective'),
			array('label'=>'Precondition', 'width'=>200, 'index'=>'precondition'),
			array('label'=>'Steps', 'width'=>200, 'index'=>'steps'),
			array('label'=>'Expected Result', 'width'=>200, 'index'=>'expected_result'),
			array('label'=>'Create Time', 'width'=>200, 'index'=>'created'),
			array('label'=>'Last Update', 'width'=>200, 'index'=>'updated'),
			array('label'=>'Owner', 'width'=>200, 'index'=>'owner_id'),
			array('label'=>'Update Comment', 'width'=>200, 'index'=>'update_comment'),
			array('label'=>'Review Comment', 'width'=>200, 'index'=>'review_comment'),
			array('label'=>'Is Active', 'width'=>200, 'index'=>'isactive'),
		);
		$this->setColumnHeader(array('rows'=>array($header)), 1);
		$header = array(
			array('label'=>'Testcase ID', 'width'=>200, 'index'=>'code'),
			array('label'=>'Summary', 'width'=>200, 'index'=>'summary'),
			array('label'=>'Module', 'width'=>200, 'index'=>'testcase_module_id'),
			array('label'=>'Testpoint', 'width'=>200, 'index'=>'testcase_testpoint_id'),
			array('label'=>'Cycle', 'width'=>200, 'index'=>'cycle'),
			array('label'=>'Test Result', 'width'=>200, 'index'=>'result_type_id'),
			array('label'=>'Project', 'width'=>200, 'index'=>'prj_id'),
			array('label'=>'Release', 'width'=>200, 'index'=>'rel_id'),
			array('label'=>'Source', 'width'=>200, 'index'=>'testcase_source_id'),
			array('label'=>'Test Environment', 'width'=>200, 'index'=>'test_env_id'),
			array('label'=>'Start Time', 'width'=>200, 'index'=>'start_time'),
			array('label'=>'Duration', 'width'=>200, 'index'=>'duration_seconds'),
			array('label'=>'Tester', 'width'=>200, 'index'=>'tester_id'),
			array('label'=>'Comment', 'width'=>200, 'index'=>'comment'),
			array('label'=>'Defects', 'width'=>200, 'index'=>'defect_ids'),
		);
		$this->setColumnHeader(array('rows'=>array($header)), 2);
		
	}
	
	protected function getData($sheetIndex){
		$db = $this->params['db']; 
		$edit_status = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
		if (is_array($this->params['prj_ids']))
			$this->params['prj_ids'] = implode(',', $this->params['prj_ids']);
		switch($sheetIndex){
			case 0: // Case Information
				$main = "SELECT tc.code, tc.summary, tc.testcase_type_id, tc.testcase_module_id, tc.testcase_testpoint_id, tc.testcase_source_id, ".
					" tc.testcase_category_id, tc.last_run, tc.isactive, ver.ver, ver.edit_status_id, ver.auto_level_id, ver.testcase_priority_id,".
					" ver.auto_run_seconds, ver.manual_run_seconds, ver.command, ver.objective, ver.precondition, ver.steps, ver.expected_result,".
					" ver.created, ver.updated, ver.updater_id, ver.owner_id, update_comment, review_comment, link.prj_id, lr.result_type_id";
				$from = " testcase tc left join prj_testcase_ver link on link.testcase_id=tc.id ".
					" left join testcase_ver ver on tc.id=ver.testcase_id".
					" left join testcase_last_result lr on lr.testcase_id=tc.id and lr.prj_id=link.prj_id";
				$where = " tc.id in ({$this->params['testcase_ids']}) AND ver.edit_status_id IN ($edit_status)";
				if (!empty($this->params['prj_ids'])){
					$where .= " AND link.prj_id IN (".$this->params['prj_ids'].")";
				}
				break;
			case 1: // Edit History
				$main = "SELECT tc.code, tc.summary, tc.testcase_type_id, tc.testcase_module_id, tc.testcase_testpoint_id, tc.testcase_source_id, ".
					" tc.testcase_category_id, tc.last_run, tc.isactive, ver.ver, ver.edit_status_id, ver.auto_level_id, ver.testcase_priority_id,".
					" ver.auto_run_seconds, ver.manual_run_seconds, ver.command, ver.objective, ver.precondition, ver.steps, ver.expected_result,".
					" ver.created, ver.updated, ver.updater_id, ver.owner_id, update_comment, review_comment";
				$from = "testcase_ver ver left join testcase tc on tc.id=ver.testcase_id";
				$where = "ver.testcase_id IN ({$this->params['testcase_ids']})";
				if (!empty($this->params['edit_history_from']))
					$where .= " AND ver.created>='".$this->params['edit_history_from']."'";
				if (!empty($this->params['edit_history_to']))
					$where .= " AND ver.created<='".$this->params['edit_history_to']."'";
				break;
			case 2: // Test History
				$main = "SELECT tc.code, tc.summary, tc.testcase_module_id, tc.testcase_testpoint_id, cycle.name as cycle, cycle.prj_id, cycle.rel_id, cd.result_type_id, cd.test_env_id, cd.start_time, cd.duration_seconds, cd.tester_id, cd.comment, cd.defect_ids";
				$from = " cycle_detail cd left join cycle on cd.cycle_id=cycle.id left join testcase tc on cd.testcase_id=tc.id";
				$where = " cd.testcase_id in ({$this->params['testcase_ids']})";
				if (!empty($this->params['test_history_from']))
					$where .= " AND cd.start_time>='".$this->params['test_history_from']."'";
				if (!empty($this->params['test_history_to']))
					$where .= " AND cd.start_time<='".$this->params['test_history_to']."'";
				if (!empty($this->params['prj_ids']))
					$where .= " AND cycle.prj_id IN ({$this->params['prj_ids']})";
				break;
			case 3: // Test Result Stat
				break;
		}
		$sql = "$main From $from Where $where";
//print_r("sql = $sql\n");		
		$res = $db->query($sql);
		$data = $res->fetchAll();
//print_r($data);		
		return $data;
	}
	
    protected function writeRow($content, $sheetIndex = 0, $defaultStyle = array(), $contentKey = null){
		foreach($content as $k=>&$v){
			if (preg_match('/(.*)_id+/', $k, $matches)){
				$key = array_search($matches[1], $this->baseTable); 
				if ($key !== false)
					$v = isset($this->baseValue[$matches[1]][$v]) ? $this->baseValue[$matches[1]][$v] : $v;
			}
		}
//print_r($content);		
		return parent::writeRow($content, $sheetIndex, $defaultStyle, $contentKey);
	}
};

class xt_zzvw_testcase_ver extends jqGridModel{
    private $editStatus;
    public function init($controller, array $options = null){
		$cart_data = new stdClass;
		$cart_data->filters = '{"groupOp":"AND","rules":[{"field":"isactive","op":"eq","data":1}]}';
        $options['db'] = 'xt';
        $options['table'] = 'zzvw_testcase_ver';
		$options['real_table'] = 'testcase_ver';
		$options['linktype'] = 'infoLink';
        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
//			'code'=>array('label'=>'Testcase ID', 'required'=>true),
			'ver'=>array('editable'=>false, 'hidden'=>false, 'width'=>100, 'formatter'=>'updateViewEditPage'),
			//'summary',
			//'testcase_type_id'=>array('label'=>'Type'),
//			'testcase_module_id'=>array('label'=>'Module', 'hidden'=>true, 'editable'=>false, 'view'=>false),
//			'testcase_testpoint_id'=>array('label'=>'Testpoint', 'hidden'=>true, 'editable'=>false, 'view'=>false),
			//'testcase_category_id'=>array('label'=>'Category'),
			//'testcase_source_id'=>array('label'=>'Source', 'hidden'=>true),
			'objective',
			'precondition',
			'expected_result'=>array('label'=>'Expcected'),
			'command',
			'resource_link'=>array('label'=>'Resource Link', 'hidden'=>true),
			'auto_level_id'=>array('label'=>'Auto Level'),
			'testcase_priority_id'=>array('label'=>'Priority'),
			'auto_run_seconds'=>array('label'=>'Auto Run Seconds', 'hidden'=>true),
			'manual_run_seconds'=>array('label'=>'Manual Run Seconds', 'hidden'=>true),
			'created'=>array('editable'=>false, 'hidden'=>true, 'view'=>false),
			'updater_id'=>array('editable'=>false, 'hidden'=>true, 'view'=>false),
			'updated'=>array('editable'=>false, 'hidden'=>true, 'view'=>false),
			'prj_ids'=>array('excluded'=>true, 'label'=>'Project', 'hidden'=>true, 'editable'=>true, 'view'=>true, 'search'=>true, 'sortable'=>true, 'edittype'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'zzvw_prj', 'cart_data'=>json_encode($cart_data)),
//			'prjs'=>array('excluded'=>true, 'label'=>'Project', 'editable'=>false, 'view'=>false),
//			'prj_note'=>array('hidden'=>true),
			'update_comment'=>array('hidden'=>true, 'label'=>'Update Comments', 'editable'=>false),
			'review_comment'=>array('hidden'=>true, 'label'=>'Review Comments', 'editable'=>false),
			'edit_status_id'=>array('editable'=>false),
			'owner_id'=>array('hidden'=>true, 'editable'=>false),
//			'isactive'=>array('editable'=>false),
//			'testcase_id'=>array('hidden'=>true, 'hidedlg'=>true, 'editable'=>false, 'view'=>false)
        );

        $options['ver'] = '1.0';
        parent::init($controller, $options);
        $this->editStatus = new KF_EditStatus($this->db, array('item_table'=>'testcase_ver'));
    } 
	
    protected function getMoreInfoForRow($row){
		$res = $this->db->query("SELECT group_concat(prj_id) as prj_ids FROM prj_testcase_ver WHERE testcase_ver_id=".$row['id']);
		$prj = $res->fetch();
		$row['prj_ids'] = $prj['prj_ids'];
		return $row;
	}

/*	
	protected function calcSql($params, $doLimit = true){
		$sqls = parent::calcSql($params, $doLimit);
		if (!empty($params['prj_ids']))
			$sqls['main'] = "SELECT * FROM zzvw_prj_testcase_ver";
		return $sqls;
	}
*/	
    public function contextMenu(){
        $menu = array(
            'publish'=>array('Publish'),
            'complete'=>array('Complete'),
            'cloneit'=>array('Clone the project'),
            'importsrs'=>array('Import SRS'),
            'exportsrs'=>array('Export SRS'),
            'importsrscasemapfile'=>array('Import SRS-Case Mapping'),
            'exportsrscasemapfile'=>array('Download SRS-Case Mapping'),
            'testreport'=>array('Generate Test Report'),
        );
        $menu = array_merge($menu, parent::contextMenu());
        return $menu;
    }
    
    public function getButtons(){
        $buttons = array(
			'ask2review'=>array('caption'=>'Ask to Review'),
			'diff'=>array('caption'=>'Tell the difference'),
/*
            'link2prj'=>array('caption'=>'(un)Link to Projects',
                'buttonimg'=>'',
                'title'=>'Link to Projects or Drop from Projects'),
			'publish'=>array('caption'=>'Publish'),
			'review'=>array('caption'=>'Review'),
			'report'=>array('caption'=>'Generate Report',
				'title'=>'Generate kinds of reports'),
*/				
			'batch_edit'=>array('caption'=>'Batch Edit',
				'title'=>'Batch Edit'),
				
        );
        $buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['tag']);
		unset($buttons['subscribe']);
		unset($buttons['change_owner']);
		return $buttons;
    }
	
	public function ask2review(){
        $params = $this->tool->parseParams('ask2review');
		if (!isset($params['from']))$params['from'] = 'testcase_ver';
//print_r($params);		
        if ($this->controller->getRequest()->isPost()){
			$elements = json_decode($params['element']);
			if (is_array($elements))
				$elements = implode(',', $elements);
			//只有Editing状态的Case可以参与Review
			// 得到对应的version id
			if($params['from'] == 'testcase')
				$cond = " testcase.id in ($elements) AND ver.prj_ids REGEXP ".$this->db->quote($this->tool->genPattern($params['prj_ids']));
			else
				$cond = "ver.id in ($elements)";
			
			$sql = "SELECT ver.id, testcase.code, testcase.summary, ver.objective, ver.precondition, ver.expected_result ".
				" FROM testcase left join testcase_ver ver on testcase.id=ver.testcase_id ".
				" WHERE $cond AND ver.edit_status_id=".EDIT_STATUS_EDITING;
				
			$res = $this->db->query($sql);
			while($row = $res->fetch()){
				$this->db->update('testcase_ver', array('edit_status_id'=>EDIT_STATUS_REVIEW_WAITING), "id=".$row['id']);
				$taskId = $this->userAdmin->addTask($params['reviewers'], 'review', 'Ask to review the testcase', '/jqgrid/jqgrid/oper/review/db/xt/table/zzvw_testcase_ver/element/'.$row['id']); // 设置一个Review的Task
				foreach($params['reviewers'] as $reviewer){
					$body = $params['ask2review_comment']."\n<BR>". // ATTACH THE DETAIL CASE INFORMATION
						" Testcase Id:".$row['code']."\n<BR>".
						" Summary:".$row['summary']."\n<BR>".
						" Objective:".$row['objective']."\n<BR>".
						" Precondition: ".$row['precondition']."\n<BR>".
						" Expected Result:".$row['expected_result']."\n<BR><BR>".
						" <a href='javascript:gen_task_dialog({$taskId[$reviewer]}, \"/jqgrid/jqgrid/oper/review/db/xt/table/zzvw_testcase_ver/element/".$row['id']."\", \"review\")'>Review the Testcase</a>";
				
					$this->userAdmin->inform($reviewer, 'Help to review the testcase', $body);	// 发送一条通知
				}
			}
		}
        else{
			$reviewers = $this->useradmin->getReviewerList(null);
			$ask2review_comment = "Hi,\n".
				" Please help to review the testcase, the base information is as following:\n";
			$this->renderView("askreview.phtml", array('reviewers'=>$reviewers, 'comment'=>$ask2review_comment));
        }
		
	}

	public function review(){
		$params = $this->tool->parseParams('review');
//	print_r($params);
		if ($this->controller->getRequest()->isPost()){
			$newComments = $this->currentUserName."[".date('Y-m-d H:i:s').": ".$params['submit']."]\n\r<BR>".$params['new_review_comments'];
			$edit_status_id = EDIT_STATUS_REVIEWING;
			$sql = "UPDATE testcase_ver set review_comment=CONCAT(".$this->db->quote($newComments).", \"\n\r<BR>\", review_comment), edit_status_id=$edit_status_id WHERE id=".$params['element'];
//print_r($sql);
			$this->db->query($sql);
			// inform the testcase owner
			if ($params['submit'] == 'accept' || $params['submit'] == 'reject'){
				$res = $this->db->query("select testcase.code, testcase.summary, ver.* from testcase_ver ver left join testcase on ver.testcase_id=testcase.id WHERE ver.id=".$params['element']);
				$row = $res->fetch();
				$body = $this->currentUserName." has reviewed the testcase, the result is ".$params['submit'].". <BR>".
					" The testcase information is <BR>".
					" Testcase Id:".$row['code']."<BR>".
					" Summarhy:".$row['summary']."<BR>".
					" Review Comment:<BR>".$params['new_review_comments'];
				$this->userAdmin->inform($row['owner_id'], 'Review Result By '.$this->currentUserName, $body);	// 发送一条通知
			}
		}
		else{
			$res = $this->db->query("SELECT * FROM zzvw_testcase_ver where id={$params['element']}");
			$testcase_ver = $res->fetch();
			$vers = array('Current Version'=>$testcase_ver);
			if ($testcase_ver['update_from'] != 0){
				$res = $this->db->query("SELECT * FROM zzvw_testcase_ver WHERE testcase_id={$testcase_ver['testcase_id']} AND ver={$testcase_ver['update_from']}");
				$vers['base_ver'] = $res->fetch();
			}
//print_r($rows);				
			$this->renderView("ver_review.phtml", array('vers'=>$vers, 'dir'=>'xt/zzvw_testcase_ver'));
		}
	}
	
	protected function getParamsFor_new_element($params){
//print_r($params);		
		$view_params = parent::getParamsFor_new_element($params);
		$testcase = new xt_testcase($this->controller);
		$view_params['cols'] = array_merge($testcase->getColModel(), $view_params['cols']);
		
		foreach($view_params['cols'] as $k=>&$m){
			if (!empty($m['excluded'])){
				unset($view_params['cols'][$k]);
				continue;
			}
			switch($m['name']){
				case 'testcase_testpoint_id':
				case 'testcase_module_id':
					$m['formatoptions']['value'] =
					$m['editoptions']['value'] = 
					$m['addoptions']['value'] = 
					$m['searchoptions']['value'] = array();
					break;
				case 'id':
				case 'ver':
				case 'edit_status_id':
				case 'testcase_id':
				case 'isactive':
				case 'update_comment':
				case 'review_comment':
				case 'owner_id':
					unset($view_params['cols'][$k]);
					break;
				case 'prj_ids':
					unset($m['excluded']);
					$m['view'] = true;
					$m['edittype'] = 'cart';
					$m['colspan'] = 3;
					$m['editable'] = true;
					$m['disabled'] = false;
//					print_r($m);
					break;
			}
		}
		$view_params['params'] = array('db'=>'xt', 'table'=>'zzvw_testcase_ver');
		$view_params['legend'] = 'Testcase';
		return $view_params;
	}
	
	protected function getParamsForInfoTab_view_edit($params){
		$this->config();
		$db = $this->get('db');
		$table = $this->get('table');
		$view_params = parent::getParamsForInfoTab_view_edit($params);
		foreach($view_params['colModels'] as $k=>$v){
			$view_params['verModels'][$k] = $v;
		}
		require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/xt_testcase.php');
		$testcase = new xt_testcase($this->controller);
		$testcase->config();
		$view_params['caseModels'] = $testcase->options['gridOptions']['colModel'];
		
//print_r($view_params);		
		// 补充testcase的一些信息
		$view_params['caseValue'] = array();
		$tc_id = 0;
		if (!empty($params['parent']))
			$tc_id = $params['parent'];
		else if(!empty($view_params['value']['testcase_id']))
			$tc_id = $view_params['value']['testcase_id'];
		if (!empty($tc_id)){
			$res = $this->db->query("SELECT tc.* FROM testcase tc WHERE tc.id=$tc_id");
			$view_params['caseValue'] = $res->fetch();
		}
/*		
		$testcase_fields = array('code', 'summary', 'testcase_module_id', 'testcase_testpoint_id', 'testcase_category_id', 'testcase_source_id', 'testcase_type_id');
		foreach($view_params['colModels'] as $k=>$v){
			if (in_array($v['name'], $testcase_fields))
				$view_params['caseModels'][$k] = $v;
			else
				$view_params['verModels'][$k] = $v;
		}
*/		
		$view_params['dir'] = 'xt/zzvw_testcase_ver';
		$view_params['parent'] = $params['parent'];
//print_r($params);		
//print_r($view_params);	
		return $view_params;
	}
	
/*
	protected function getParamsForInfoTab_test_history($params){
		$db = $this->get('db');
		$table = $this->get('table');
		// get the test history
		$res = $this->db->query("SELECT * FROM zzvw_cycle_detail WHERE testcase_ver_id=".$params['id']);
		$test_history = $res->fetchAll();
		$view_params = array('label'=>'Test History', 'db'=>$db, 'table'=>$table, 'id'=>$params['element'], 'disabled'=>empty($params['element']),
			'dir'=>'xt/zzvw_testcase_ver', 'test_history'=>$test_history);
		return $view_params;
	}
*/
    public function link2prj(){ // 可能有testcase发起，也可能有testcase_ver发起，有params['from']区分
        $params = $this->tool->parseParams('testcase_ver_link2prj');
		if(empty($params['from']))$params['from'] = 'testcase_ver';
		$element = $params['element'] = json_decode($params['element']);
//print_r($element);
//print_r($params);
        if ($this->controller->getRequest()->isPost()){
			$this->_link2prj($params);//$params['element'], $params['projects'], $params['link']);
        }
        else{
			//如果只选中了一个testcase，那么就将该case的连接情况都显示出来，否则，就列出Active且Ongoing的Projects
			$projects = array();
			$res = $this->db->query("SELECT prj.id, prj.name FROM prj WHERE prj.isactive=".ISACTIVE_ACTIVE." and prj.prj_status_id=".PRJ_STATUS_ONGOING);
			while($row = $res->fetch()){
				$projects[$row['id']] = $row;
			}
//print_r($projects);
			if(count($element) == 1){
				$ver_id = 0;
				if ($params['from'] == 'testcase'){
					$res = $this->db->query("select testcase_ver_id FROM prj_testcase_ver WHERE testcase_id={$element[0]} AND prj_id={$params['prj_ids']} AND edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")");
					if ($row = $res->fetch())
						$ver_id = $row['testcase_ver_id'];
				}
				else
					$ver_id = $element[0];
				if (!empty($ver_id)){
					$sql = "SELECT prj_id FROM prj_testcase_ver WHERE testcase_ver_id=$ver_id AND edit_status_id in (".EDIT_STATUS_PUBLISHED.", ".EDIT_STATUS_GOLDEN.")";
//	print_r($sql);				
					$res = $this->db->query($sql);//"SELECT prj_id FROM prj_testcase_ver where testcase_ver_id={$element[0]}");
					while($row = $res->fetch()){
						if (isset($projects[$row['prj_id']])){
							$projects[$row['prj_id']]['linked'] = true;
						}
					}
				}
			}
//print_r($projects);				
			$this->renderView("testcase_ver_link2prj.phtml", compact('projects'));//array('projects'=>$rows));
        }
    }
	
	protected function _link2prj($params){//$vers, $prj_ids, $link = 'link', $note = ''){
		$strTestcase_ids = implode(',', $params['element']);
		if (!isset($params['note'])) $params['note'] = '';
		$edit_statuses = EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN;
		if ($params['from'] == 'testcase'){
			$sql = "SELECT * FROM testcase_ver WHERE testcase_id IN ($strTestcase_ids) AND edit_status_id in ($edit_statuses) AND prj_ids REGEXP ".$this->db->quote($this->tool->genPattern($params['prj_ids']));
		}
		else
			$sql = "SELECT * FROM testcase_ver WHERE id IN ($strTestcase_ids) AND edit_status_id in ($edit_statuses)";
		$res = $this->db->query($sql);
		while($row = $res->fetch()){
			$current_prjs = explode(',', $row['prj_ids']);
//print_r($current_prjs);		
//print_r($params['projects']);
			if ($params['link'] == 'link'){
				$new_prj = array_diff($params['projects'], $current_prjs); //这是要添加的记录。添加前要先删除原有记录
				$last_prj = array_merge($current_prjs, $new_prj); //最终应挂接的projects
			}
			else{
				$new_prj = array_intersect($params['projects'], $current_prjs); // 这是要删除的记录
				$last_prj = array_diff($current_prjs, $params['projects']); //最终应挂接的projects
			}
//print_r($new_prj);
//print_r($last_prj);
			if (!empty($new_prj)){
				// 删除记录
				// 在prj_testcase_ver_history里插入相应记录
				$sql = "INSERT INTO prj_testcase_ver_history (prj_id, testcase_id, testcase_ver_id, act, note) ".
					" SELECT prj_id, testcase_id, testcase_ver_id, 'remove', ".$this->db->quote($params['note']).
					" FROM prj_testcase_ver".
					" WHERE testcase_ver_id={$row['id']} AND prj_id in (".implode(',', $new_prj).")";
//	print_r($sql);			
				$this->db->query($sql);
				//在prj_testcase_ver里删除new_prj + version
				$this->db->delete('prj_testcase_ver', 'prj_id in ('.implode(',', $new_prj).') AND testcase_ver_id='.$row['id']);
				if($params['link'] == 'link'){
					// 在prj_testcase_ver_history里插入相应记录
					foreach($new_prj as $e){
						$this->db->insert('prj_testcase_ver_history', array('prj_id'=>$e, 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$row['id'], 'act'=>'link', 'note'=>$params['note']));
						$this->db->insert('prj_testcase_ver', array('prj_id'=>$e, 'testcase_id'=>$row['testcase_id'], 'testcase_ver_id'=>$row['id'], 'note'=>$params['note'], 
							'edit_status_id'=>$row['edit_status_id'], 'testcase_priority_id'=>$row['testcase_priority_id'], 'owner_id'=>$row['owner_id']));
					}
				}
			}
			// 更新Version表里对应的prj_ids
			$this->db->update('testcase_ver', array('prj_ids'=>implode(',', $last_prj)), "id=".$row['id']);
		}
		return;
	}
	
	public function report(){ // element_id is testcase_id
		$params = $this->tool->parseParams('report');
		if ($this->controller->getRequest()->isPost()){
//print_r($params);
			// 根据参数生成报表：包含的Projects，Edit_history, Test_history
			// Sheets: Cover, Case Information, Edit History, Test History, Test Result Stat
			$report_params = $params;
			$report_params['db'] = $this->db;
			$report_params['testcase_ids'] = implode(',', json_decode($params['element']));
//print_r($report_params);
			$report = new ver_report($report_params);
			$sheets = array(0);
			if (!empty($params['edit_history']))
				$sheets[] = 1;
			if (!empty($params['test_history']))
				$sheets[] = 2;
//print_r($sheets);			
			$report->report($sheets);
			return $report->save('aaa');
		}
		else{
			// 需要选择：是否包含Edit History？Edit From To？是否包含Test History？Test From To？包含哪些Projects？
			$cols = array(
				array('name'=>'prj_ids', 'label'=>'Include Projects', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>array()), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'prj', 'cart_data'=>'""'),
				array('name'=>'edit_history', 'label'=>'Include Edit History', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'Yes')), 'defval'=>1),
				array('name'=>'edit_history_from', 'label'=>'Edit History From', 'editable'=>true, 'DATA_TYPE'=>'date', 'type'=>'date'),
				array('name'=>'edit_history_to', 'label'=>'Edit History To', 'editable'=>true, 'DATA_TYPE'=>'date', 'type'=>'date', 'defval'=>date('Y-m-d')),
				array('name'=>'test_history', 'label'=>'Include Test History', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'Yes')), 'defval'=>1),
				array('name'=>'test_history_from', 'label'=>'Test History From', 'editable'=>true, 'DATA_TYPE'=>'date', 'type'=>'date'),
				array('name'=>'test_history_to', 'label'=>'Test History To', 'editable'=>true, 'DATA_TYPE'=>'date', 'type'=>'date', 'defval'=>date('Y-m-d')),
			);
			$this->renderView("report.phtml", array('cols'=>$cols));
		}
	}
	
	private function generateHTMLHeader($title){
		$str = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">";
		$str .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
		$str .= "<head>";
		$str .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
		$str .= "<title>$title</title>";
		$str .= "<script type='text/javascript'>
			function displayDiv(id){
				var div = document.getElementById(id);
				if (div){
					var display = div.style.display;
					if (display == 'none')
						display = 'block';
					else
						display = 'none';
					div.style.display = display;
				}
			}
			</script>";
		$str .= "<style type='text/css'>
		<!--
		a:link {
			text-decoration: none;
		}
		a:visited {
			text-decoration: none;
		}
		a:hover {
			text-decoration: none;
		}
		a:active {
			text-decoration: none;
		}
		div{
			position:relative; left:30px;
		}
		.module{
			background-color: #DDDDDD;
			
		}
		.moduledesc{
			background-color: #EEEEEE;
		}
		.case{
			background-color: #DDDDDD;
		}
		-->
		</style>
		";
		
		$str .= "</head>";
		return $str;
	} 

	private function generateIntro($params){
		if(empty($params['intro']))
			$params['intro'] = "Intro";
		$str = "<a href='javascript:displayDiv(\"div_intro\")'><strong>Introduction</strong></a><BR />";
		$str .= "<div id='div_intro' style='display:none'>";
		$str .= $params['intro'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateSetup($params){
		if (empty($params['setup']))
			$params['setup'] = "Setup";
		$str = "<a href='javascript:displayDiv(\"div_setup\")'><strong>Setup Test Environment</strong></a><BR />";
		$str .= "<div id='div_setup' style='display:none'>";
		$str .= $params['setup'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateTestCase($params){
		$module = "";
		$str = "";
		$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, ".
			" auto_level.name as auto_level, ver.manual_run_seconds, ver.auto_run_seconds, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
			" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
			" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" left join testcase_category category on tc.testcase_category_id=category.id".
			" left join testcase_source source on tc.testcase_source_id=source.id".
			" left join auto_level on ver.auto_level_id=auto_level.id".
			" WHERE tc.id in (".implode(',', json_decode($params['element'])).") and link.prj_id=".$params['prj_ids'].
			" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
			" ORDER BY module ASC";
		$res = $this->db->query($sql);
		$change = false;
		while($row = $res->fetch()){
			if ($row['module'] != $module){//module切换
				if (!empty($module))
					$str .= "</div>";
				$module = $row['module'];
				$str .= "<BR /><a href='javascript:displayDiv(\"div_module_{$row['module_id']}\")'><strong>{$row['module']}</strong></a><BR />";
				$str .= "<div id='div_module_{$row['module_id']}' style='display:none'>";
			}
			$str .= $this->generateTCHTML($row);
		}
		$str .= "</div>";
		return $str;
	}
	
	private function generateTCHTML($row){
		$fields = array('summary'=>'Name',
						'category'=>'Category',
						'auto_level'=>'Auto Level',
						'manual_run_seconds'=>'Manual Run Time',
						'auto_run_seconds'=>'Auto Run Time',
						'command'=>'Command',
						'objective'=>'Objective',
						'precondition'=>'Environment',
						'steps'=>'Steps',
						'expected_result'=>'Expected Result',
		);
		$str = "<p /><a href='javascript:displayDiv(\"div_case_{$row['id']}\")'>".$row['code'].":".$row['summary']."</a>";
		$str .= "<div id='div_case_{$row['id']}' style='display:none'><table border='1' bgcolor='#EEEEEE' width='100%'>";
		
		$currentRow = 0;
		foreach($fields as $key=>$caption){
			$content = $row[$key];
			if ($key == 'command' || $key == 'objective' || $key == 'steps' || $key == 'precondition' || $key == 'expected_result')
				$content = str_replace("\n", '<br />', $content);
			if (empty($content))
				$content = "&nbsp";
			$bgColor = '#CCCCCC';
			if ($currentRow % 2)
				$bgColor = '#DDDDDD';
			$str .= sprintf("<tr style='background-color:%s; color:blue'>
				<td width='15%%'>%s:</td>
				<td width='85%%'>%s</td>
				</tr>\n", $bgColor, $caption, $content);
			$currentRow ++;        
		}
		$str .= "</table></div>\n";
		return $str;
	}
	
    public function ver_report(){
        $params = $this->tool->parseParams('ver_report');
		$fileName = "tmp.txt";
        if ($this->controller->getRequest()->isPost()){
//print_r($params);
			$str = '';
			switch($params['report_type']){
				case 1: // Release Report
					$fileName = "case_release.html";
					$str = $this->generateHTMLHeader("Case Release");
					$str .= "<body>";
					$str .= $this->generateIntro($params);
					$str .= $this->generateSetup($params);
					$str .= $this->generateTestCase($params);
					$str .= "</body></html>";
					break;
				case 2: // cmd file for linux BSP
					$fileName = "cmd4linuxbsp.txt";
					$sql = "SELECT tc.code, ver.command ".
						" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" WHERE tc.id in (".implode(',', json_decode($params['element'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->db->query($sql);
					while($row = $res->fetch()){
						$str .= $row['code'].' '.$row['command']."\n";
					}
					break;
				case 3://cmd file for codec 
					$fileName = "cmd4codec.txt";
/*					
<playlist>
  <testcase>
      <title>AAC_LC_24kHz_128kbps_2_Main.aac</title>
      <module>AACLCDec</module>
      <cmdline></cmdline>
      <location>\AACLCDec\Conformance\ADIF\</location>
  </testcase>
*/					
					$str = "<playlist>";
					$sql = "SELECT tc.code, ver.command, module.name as module, ver.resource_link ".
						" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" left join testcase_module module on tc.testcase_module_id=module.id".
						" WHERE tc.id in (".implode(',', json_decode($params['element'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->db->query($sql);
					while($row = $res->fetch()){
						$str .= "\n\t<testcase>".
							"\n\t\t<title>{$row['code']}</title>".
							"\n\t\t<module>{$row['module']}</module>".
							"\n\t\t<cmdline>{$row['command']}</cmdline>".
							"\n\t\t<location>{$row['resource_link']}</location>".
							"\n\t</testcase>";
					}
					$str .= "\n</playlist>";
					break;
				case 4: // xml cmd file for codec
					$fileName = "cmd4codec.xml";
					$str = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
					$str .= "<!--\n";
					$str .= "XML playlist used for Freescale codec test, based on XiaoTian test system.\n";
					$str .= "-->\n";
					$str .= "<playlist>\n";
					
					$sql = "SELECT tc.code, tc.summary, testcase_type.name as type, module.name as module, testpoint.name as testpoint, testcase_category.name as category, ".
						" testcase_priority.name as priority, testcase_source.name as source, auto_level.name as auto_level, ".
						" ver.command, ver.resource_link, ver.objective, ver.precondition, ver.steps, ver.expected_result, ".
						" stream.name, stream.location, codec_stream_container.name as container, codec_stream_v4cc.name as v4cc,".
						" stream.v_width, stream.v_height, stream.v_framerate, stream.v_bitrate, stream.chromasubsampling,".
						" stream.v_track, codec_stream_a_codec.name as a_codec, stream.a_samplerate, stream.a_channel, stream.a_track, stream.a_bitrate,".
						" stream.subtitle, stream.duration".
						" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" left join testcase_type on tc.testcase_type_id=testcase_type.id".
						" left join testcase_module module on tc.testcase_module_id=module.id".
						" left join testcase_source on tc.testcase_source_id=testcase_source.id".
						" left join testcase_priority on ver.testcase_priority_id=testcase_priority.id".
						" left join auto_level on ver.auto_level_id=auto_level.id".
						" left join testcase_category on tc.testcase_category_id=testcase_category.id".
						" left join testcase_testpoint testpoint on tc.testcase_testpoint_id=testpoint.id".
						" left join codec_stream stream on stream.testcase_id=tc.id".
						" left join codec_stream_container on stream.codec_stream_container_id=codec_stream_container.id".
						" left join codec_stream_v4cc on stream.codec_stream_v4cc_id=codec_stream_v4cc.id".
						" left join codec_stream_a_codec on stream.codec_stream_a_codec_id=codec_stream_a_codec.id".
						" WHERE tc.id in (".implode(',', json_decode($params['element'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->db->query($sql);
					while($row = $res->fetch()){
						$str .= "  <teststream id=\"". $row['code'] . "\">\n";
						$str .= "      <caseinfo>\n";
						$str .= "          <type>" . $row['type'] . "</type>\n";
						$str .= "          <module>" . $row['module'] . "</module>\n";
						$str .= "          <testpoint>" . $row['testpoint'] . "</testpoint>\n";
						$str .= "          <category>" . $row['category'] . "</category>\n";
						$str .= "          <priority>" . $row['priority'] . "</priority>\n";
						$str .= "          <source>" . $row['source'] . "</source>\n";
						$str .= "          <autolevel>" . $row['auto_level'] . "</autolevel>\n";
						$str .= "          <objective><![CDATA[" . $row['objective'] . "]]></objective>\n";
						$str .= "          <environment><![CDATA[" . $row['precondition'] . "]]></environment>\n";
						$str .= "          <steps><![CDATA[" . $row['steps'] . "]]></steps>\n";
						$str .= "          <expected><![CDATA[" . $row['expected_result'] . "]]></expected>\n";
						$str .= "          <cmdline><![CDATA[" . $row['command'] . "]]></cmdline>\n";
						$str .= "          <location><![CDATA[" . $row['resource_link'] . "]]></location>\n";
						$str .= "      </caseinfo>\n";
						$str .= "      <streaminfo>\n";
						$str .= "          <clipname><![CDATA[" . $row['summary'] . "]]></clipname>\n";
						$str .= "          <container>" . $row['container'] . "</container>\n";
						$str .= "          <video>\n";
						$str .= "              <v4cc>" . $row['v4cc'] . "</v4cc>\n";
						$str .= "              <v_width>" . $row['v_width'] . "</v_width>\n";
						$str .= "              <v_height>" . $row['v_height'] . "</v_height>\n";
						$str .= "              <v_framerate>" . $row['v_framerate'] . "</v_framerate>\n";
						$str .= "              <v_bitrate>" . $row['v_bitrate'] . "</v_bitrate>\n";
						$str .= "              <chromasubsampling>" . $row['chromasubsampling'] . "</chromasubsampling>\n";
						$str .= "              <v_track>" . $row['v_track'] . "</v_track>\n";
						$str .= "          </video>\n";
						$str .= "          <audio>\n";
						$str .= "              <a_codec>" . $row['a_codec'] . "</a_codec>\n";
						$str .= "              <a_samplerate>" . $row['a_samplerate'] . "</a_samplerate>\n";
						$str .= "              <a_bitrate>" . $row['a_bitrate'] . "</a_bitrate>\n";
						$str .= "              <a_channel>" . $row['a_channel'] . "</a_channel>\n";
						$str .= "              <a_track>" . $row['a_track'] . "</a_track>\n";
						$str .= "          </audio>\n";
						$str .= "          <duration>" . $row['duration'] . "</duration>\n";
						$str .= "          <others>\n";
						$str .= "              <subtitle>" . $row['subtitle'] . "</subtitle>\n";
						$str .= "          </others>\n";
						$str .= "      </streaminfo>\n";
						$str .= "  </teststream>\n";
					}
					$str .= "</playlist>";					
					break;
			}
			$fileName = $this->tool->saveFile($str, $fileName);
			return $fileName;
        }
    }
	
	/*
		publish可能有两个地方发起:testcase发起或testcase_ver发起,区别在于id的含义,一个指向testcase_id,一个指向testcase_ver_id.谁发起在参数from指定,默认为testcase发起
		*/
	public function publish(){
        $params = $this->tool->parseParams('publish');
		if (empty($params['from']))$params['from'] = 'testcase_ver';
		$strEditStatus = EDIT_STATUS_EDITING.','.EDIT_STATUS_REVIEW_WAITING.','.EDIT_STATUS_REVIEWING.','.EDIT_STATUS_REVIEWED;
		$strPrj = '';
		$strTestcaseIds = implode(',', json_decode($params['element']));
		if($params['from'] == 'testcase'){
			$res = $this->db->query("SELECT testcase_ver_id FROM prj_testcase_ver WHERE testcase_id in ($strTestcaseIds) AND prj_id={$params['prj_ids']} AND edit_status_id in ($strEditStatus)");
			while($row = $res->fetch())
				$ver[] = $row['testcase_ver_id'];
			$strTestcaseIds = implode(',', $ver);
		}
		$sql = 'UPDATE testcase_ver ver, prj_testcase_ver link SET ver.edit_status_id='.EDIT_STATUS_PUBLISHED.
			', ver.update_comment=concat(update_comment, "\n\r['.$this->currentUserName.' At '.date('Y-m-d H:i:s').']\n\r", :note)'.
			', link.edit_status_id='.EDIT_STATUS_PUBLISHED.
			' WHERE ver.id in ('.$strTestcaseIds.') AND ver.edit_status_id IN ('.$strEditStatus.') AND link.testcase_ver_id=ver.id';
//print_r($sql);
		$this->db->query($sql, array('note'=>$params['note']));
		return;
    }

	protected function before_cloneit(&$valuePair){
		parent::before_cloneit($valuePair);
		unset($valuePair['testcase_id']);
	}
    
    protected function _saveOne($db, $table, $pair){
//print_r($pair);
		$vs = $this->tool->extractData($pair, 'testcase_ver', 'xt');
		$prj_ids = array();
		if (!empty($vs['prj_ids'])){
			$prj_ids = $vs['prj_ids'];
			$vs['prj_ids'] = implode(',', $prj_ids);
		}
//print_r($vs);	
		if(empty($vs['owner_id']))$vs['owner_id'] = $this->currentUser;
		if(empty($vs['edit_status_id']))$vs['edit_status_id'] = EDIT_STATUS_EDITING;
		$case = $this->tool->extractData($pair, 'testcase', 'xt');
		// check if the case exist
		if (!empty($pair['id'])){
			$case['id'] = $pair['testcase_id'];
		}
		// we should create the case first
		$case_id = parent::_saveOne($db, 'testcase', $case);
		$vs['testcase_id'] = $case_id;
		$last_edit_status = $vs['edit_status_id'];
		$newVersionStatus = array(EDIT_STATUS_PUBLISHED, EDIT_STATUS_GOLDEN);
		if (in_array($last_edit_status, $newVersionStatus)){
			$vs['update_from'] = $vs['ver'];
			$res = $this->db->query("select max(ver) as max_ver FROM testcase_ver WHERE testcase_id=".$vs['testcase_id']);
			$row = $res->fetch();
			$vs['ver'] = $row['max_ver'] + 1;
			$vs['edit_status_id'] = EDIT_STATUS_EDITING;
			$lastVer = $vs['id'];
			unset($vs['id']);
		}
		$newVer = parent::_saveOne($db, 'testcase_ver', $vs);
		$link = array('testcase_id'=>$case_id, 'testcase_ver_id'=>$newVer, 'owner_id'=>$vs['owner_id'], 'testcase_priority_id'=>$vs['testcase_priority_id'], 'edit_status_id'=>$vs['edit_status_id']);
//		$this->db->query("DELETE FROM prj_testcase_ver WHERE testcase_ver_id=$newVer");
		foreach($prj_ids as $prj_id){
			$link['prj_id'] = $prj_id;
			$this->db->insert('prj_testcase_ver', $link);
		}
		if (in_array($last_edit_status, $newVersionStatus)){
			//复制Steps
			$sql = "INSERT INTO testcase_ver_step (testcase_ver_id, step_number, description, expected_result, params, auto_level_id, isactive)".
				" SELECT $newVer, step_number, description, expected_result, params, auto_level_id, isactive".
				" FROM testcase_ver_step WHERE testcase_ver_id=".$lastVer;
			$this->db->query($sql);
		}
		return $newVer;
    }
	
	public function checkUnique(){
		$params = $this->tool->parseParams(); // field, value
		$res = $this->db->query("SELECT * FROM testcase WHERE {$params['field']}=:value limit 0, 2", array('value'=>$params['value']));
		$rows = $res->rowCount();
		if ($rows == 1){
			$row = $res->fetch();
			if ($row['id'] == $params['id'])
				return 1;
			else
				return 2;
		}
		if ($rows == 0)
			return 1;
		return $rows;
	}
	
	public function diff(){
		$params = $this->tool->parseParams('diff');
//		print_r($params);
		$rows = array();
		$res = $this->db->query("SELECT * FROM zzvw_testcase_ver WHERE id IN (".implode(',', json_decode($params['vers'])).")");
		while($row = $res->fetch())
			$rows[' [version '.$row['ver']."]"] = $row;
        if ($this->controller->getRequest()->isPost()){
			// export to excel
			
		}
		else{
			$this->renderView("testcase_ver_diff.phtml", array('vers'=>$rows));
		}
	}
	
	public function listFromCycle(){
		$params = $this->tool->parseParams();
print_r($params);
		// get the prj_id from cycle_id
		
	}

}
