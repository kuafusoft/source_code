<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_resultInfo extends action_jqgrid{

    public function handlePost(){
		$params = $this->params;
		if (!empty($params['id'])){	
			$logFileList = array();
			$sql = "SELECT test_env_id, result_type_id, tester_id, logFile, codec_stream_id, testcase_id, issue_comment, comment, defect_ids".
				" FROM cycle_detail WHERE id=".$params['id'];
			$res = $this->tool->query($sql);
			$detail = $res->fetch();
			if($detail){
				$res = $this->tool->query("SELECT code FROM testcase WHERE id=".$detail['testcase_id']);
				$code = $res->fetch();			
				$res = $this->tool->query("SELECT id, name FROM result_type");
				$results['-1'] = '==blank==';
				while($result = $res->fetch())
					$results[$result['id']] = $result['name'];				
				$res = $this->tool->query("SELECT id, name FROM test_env");
				//$envs[0] = '';
				while($env = $res->fetch())
					$envs[$env['id']] = $env['name'];
				if(!empty($detail['logFile'])){
					$logFileList = explode(";", $detail['logFile']);
					$path = APPLICATION_PATH."/log/".$params['parent']."/".$params['id'];//$path = $main_path."/".$code['code']."_".$params['id'];
					$path = $this->tool->uniformFileName($path);

					foreach($logFileList as &$fileInfo){
						$fn = explode(" ", $fileInfo);
						$fileName = $this->tool->uniformFileName($path."/".$fn[0]);
						if(file_exists($fileName)){
							$fileInfo = $path."/".$fileInfo;
						}
					}
				}
// print_r($logFileList);				
				$cols = array(
					array('id'=>'code', 'name'=>'code', 'label'=>'Test Case', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$code['code']),
					array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$detail['test_env_id'], 'editoptions'=>array('value'=>$envs), 'editrules'=>array('required'=>true)),
					array('id'=>'result_type_id', 'name'=>'result_type_id', 'label'=>'result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$params['result_type_id'], 'editoptions'=>array('value'=>$results), 'editrules'=>array('required'=>true)),
					array('id'=>'comment', 'name'=>'comment', 'label'=>'Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea', 'defval'=>$detail['comment']),
					//array('id'=>'new_comment', 'name'=>'new_comment', 'label'=>'New CR Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
					array('id'=>'defect_ids', 'name'=>'defect_ids', 'label'=>'CRID/JIRA Key', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$detail['defect_ids']),
					//array('id'=>'file_format', 'name'=>'file_format', 'label'=>'Log Foramt', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$format)),
					array('id'=>'logfile', 'name'=>'logfile', 'label'=>'Logfile', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file', 'editoptions'=>array('value'=>$logFileList)),
					array('id'=>'issue_comment', 'name'=>'issue_comment', 'label'=>'Issue Comment', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'textarea', 'defval'=>$detail['issue_comment']),
					array('id'=>'new_issue_comment', 'name'=>'new_issue_comment', 'label'=>'New Issue Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
					array('name'=>'submit_username', 'label'=>'JIRA User', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
					array('name'=>'submit_password', 'label'=>'JIRA PWD', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'password'),
					array('id'=>'submit_bug', 'name'=>'submit_bug', 'label'=>'submit To JIRA', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 
						'editoptions'=>array('value'=>array(1=>'submit'))),
				);
				$btn = true;
				$this->renderView('newElement.phtml', array('cols'=>$cols, 'id'=>$params['id'], 'btn'=>$btn), '/jqgrid/xt/'.$this->get('table'));
			}
		}
	}
	
}

?>