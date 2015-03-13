<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle/action_save.php');

class xt_zzvw_cycle_action_cloneall extends xt_zzvw_cycle_action_save{

	public function handlePost(){
		$valuePairs = $this->parseParams();
		if(!empty($valuePairs['myname'])){
			//$valuePairs['id'] = json_decode($valuePairs['id']);
			$res = $this->tool->query("SELECT * FROM cycle WHERE id in (".implode(',', $valuePairs['id']).")");
			while($row = $res->fetch()){
				$old_id = $row['id'];
				unset($row['id']);
				if(!empty($valuePairs['rel_id']))
					$row['rel_id'] = $valuePairs['rel_id'];
				$row['start_date'] = date('Y-m-d');
				if(!empty($valuePairs['start_date']))
					$row['start_date'] = $valuePairs['start_date'];
				$row['end_date'] = 0;
				if(!empty($valuePairs['end_date']))
					$row['end_date'] = $valuePairs['end_date'];
				if(!empty($valuePairs['myname']))
					$row['name'] = $row['name'].'_'.$valuePairs['myname'];
				else
					$row['name'] = $row['name'].'_clone';
				$row['cycle_status_id'] = CYCLE_STATUS_ONGOING;
				$row['isactive'] = ISACTIVE_ACTIVE;	
				$row['creater_id'] = $this->userInfo->id;
				$row['cloned_id'] = $old_id;
				//check unique
				$affectedID = $this->save('xt', 'cycle', $row);
				$sql = "INSERT INTO cycle_detail (cycle_id, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, build_result_id, tester_id)".
					" SELECT $affectedID, testcase_ver_id, testcase_id, codec_stream_id, test_env_id, 1, tester_id FROM cycle_detail WHERE cycle_id=".$old_id;
				$this->tool->query($sql);
				$this->updateTestCaseVer($affectedID, '');
				$this->tool->log('save', $valuePairs);
			}
			$errorCode['code'] = ERROR_OK;
			$errorCode['msg'] = $affectedID;
			return $errorCode;
		}
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['type'] = 'Result';
		$view_params['view_file'] = 'newElement.phtml';
		$view_params['view_file_dir'] = '/jqgrid';
		$view_params['blank'] = 'false';
		
		$rel[0] = '';
		$res = $this->tool->query("SELECT id, name FROM rel");
		while($row = $res->fetch()){
			$rel[$row['id']] = $row['name'];
		}
		$cols = array(
			array('id'=>'myname', 'name'=>'myname', 'label'=>'My name', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'editrules'=>array('required'=>true)),
			array('id'=>'rel_id', 'name'=>'rel_id', 'label'=>'Rel', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$rel), 'editrules'=>array('required'=>true)),
			array('id'=>'start_date', 'name'=>'start_date', 'label'=>'Start Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true)),
			array('id'=>'end_date', 'name'=>'end_date', 'label'=>'End Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true))
		);
		$view_params['cols'] = $cols;
		return $view_params;
	}
}
?>