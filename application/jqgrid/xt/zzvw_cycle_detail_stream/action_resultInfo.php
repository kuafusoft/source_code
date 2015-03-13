<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_resultInfo.php');

class xt_zzvw_cycle_detail_stream_action_resultInfo extends xt_zzvw_cycle_detail_action_resultInfo{

	public function handlePost(){
		$params = $this->parseParams();
		if (!empty($params['id'])){	
			$sql = "SELECT creater_id, assistant_owner_id FROM cycle WHERE id=".$params['parent'];
			$res = $this->tool->query($sql);
			$info = $res->fetch();
			$sql = "SELECT id, d_code, codec_stream_id, cycle_id, tester_id, test_env_id FROM zzvw_cycle_detail_stream WHERE id=".$params['id'];
			$res = $this->tool->query($sql);
			$detail = $res->fetch();
			if($detail){	
				// if($params['result_type_id'] == 2){
					// $res = $this->tool->query("select comment, defect_ids, result_type_id from zzvw_cycle_detail_stream where cycle_id = ".$detail['cycle_id']." and codec_stream_id=".$detail['codec_stream_id']);
					// while($info = $res->fetch()){
						// $d['result_type_id'][] = $info['result_type_id'];
						// if(!empty($info['comment']) && $info['comment'] != '')
							// $d['comment'][] = $info['comment'];
						// if(!empty($info['defect_ids']) && $info['defect_ids'] != '')
							// $d['defect_ids'][] = $info['defect_ids'];
					// }
				// }
				$res = $this->tool->query("SELECT id, name FROM result_type");
				$results['-1'] = '==blank==';
				while($result = $res->fetch())
					$results[$result['id']] = $result['name'];				
				$res = $this->tool->query("SELECT id, name FROM test_env");
				//$envs[0] = '';
				while($env = $res->fetch())
					$envs[$env['id']] = $env['name'];					
				// $format = array('', 'txt', 'excel', 'yml', 'html', 'zip');
				$cols = array(
					array('id'=>'code', 'name'=>'code', 'label'=>'Stream', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'text', 'defval'=>$detail['d_code']),
					array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$detail['test_env_id'], 'editoptions'=>array('value'=>$envs), 'editrules'=>array('required'=>true)),
					array('id'=>'result_type_id', 'name'=>'result_type_id', 'label'=>'result', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'defval'=>$params['result_type_id'],'editoptions'=>array('value'=>$results), 'editrules'=>array('required'=>true)),
					array('id'=>'comment', 'name'=>'comment', 'label'=>'CR Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
					array('id'=>'defect_ids', 'name'=>'defect_ids', 'label'=>'CR', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
					//array('id'=>'file_format', 'name'=>'file_format', 'label'=>'Log Foramt', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$format)),
					array('id'=>'logfile', 'name'=>'logfile', 'label'=>'Logfile', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file'),
					array('id'=>'issue_comment', 'name'=>'issue_comment', 'label'=>'Issue Comment', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
					array('id'=>'new_issue_comment', 'name'=>'new_issue_comment', 'label'=>'New Issue Comment', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'textarea'),
					//array('id'=>'submit_a_cr', 'name'=>'submit_a_cr', 'label'=>'Submit A CR', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>array(1=>'submit'))),
				);
				$btn = true;
				$this->renderView('newElement.phtml', array('cols'=>$cols, 'id'=>$params['id'], 'btn'=>$btn), '/jqgrid/xt/'.$this->get('table'));
			}
		}
	}
}

?>