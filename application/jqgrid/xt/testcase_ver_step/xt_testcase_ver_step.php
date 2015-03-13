<?php
/*
版本管理上需要注意：
1. 如果当前Case是Published，那么修改Step和修改Version内容一样应生成一个新的Version
2. 如果当前版本还没有Publish，不生成新的Version
3. 生成新Version的时候，应同时复制一份Steps
*/
require_once('jqgridmodel.php');
require_once('kf_editstatus.php');

class xt_testcase_ver_step extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'xt';
        $options['table'] = 'testcase_ver_step';
        $options['columns'] = array(
			'id', 
			'testcase_ver_id'=>array('hide'=>true, 'hidedlg'=>true, 'editable'=>false, 'view'=>false, 'formatter'=>'text'),
			'step_number'=>array('label'=>'Step Number', 'editable'=>false, 'view'=>false, 'formatter'=>'step_number'),
			'description',
			'expected_result'=>array('label'=>'Expected Result'),
//			'params',
			'auto_level_id'=>array('label'=>'Auto Level'),
//			'isactive'
		);
        $options['ver'] = '1.0';
		$options['sortableRows'] = true;
		$options['navOptions']['del'] = true;
        parent::init($controller, $options);
    } 
	
	public function getButtons(){
		$buttons = parent::getButtons();
		unset($buttons['export']);
		unset($buttons['tag']);
		$buttons['save_order'] = array('caption'=>'Save Order');
		return $buttons;
	}
	
	public function save_order(){
		$params = $this->tool->parseParams();
		print_r($params);
		$step_number = 1;
		foreach($params['step_ids'] as $step_id){
			if(empty($step_id))
				continue;
			$this->db->update('testcase_ver_step', array('step_number'=>$step_number ++), "id=$step_id");
		}
	}
	
	protected function _saveOne($db, $table, $pair){
		if (empty($pair['testcase_ver_id']) && !empty($pair['parent']))
			$pair['testcase_ver_id'] = $pair['parent'];
		// 检查Version的状态
		$res = $this->db->query("SELECT * FROM testcase_ver WHERE id=".$pair['testcase_ver_id']);
		$ver = $res->fetch();
		$newVersionStatus = array(EDIT_STATUS_PUBLISHED, EDIT_STATUS_GOLDEN);
		if (in_array($ver['edit_status_id'], $newVersionStatus)){
			// create a new case version
			$res_max_ver = $this->db->query("select max(ver) as max_ver from testcase_ver WHERE testcase_id=".$ver['testcase_id']);
			$tmp = $res_max_ver->fetch();
			$ver['ver'] = $tmp['max_ver'] + 1;
			$ver['id'] = 0;
			$ver['edit_status_id'] = EDIT_STATUS_EDITING;
			$ver['update_from'] = $pair['testcase_ver_id'];
			$this->db->insert('testcase_ver', $ver);
			$newVerId = $this->db->lastInsertId();
			// insert the all steps for the version
			$sql = "INSERT INTO testcase_ver_step (testcase_ver_id, step_number, description, expected_result, params, auto_level_id, isactive)".
				" SELECT $newVerId, step_number, description, expected_result, params, auto_level_id, isactive".
				" FROM testcase_ver_step WHERE testcase_ver_id=".$pair['testcase_ver_id'];
			$this->db->query($sql);
			$pair['testcase_ver_id'] = $newVerId;
		}
		// update the step_number
		$res = $this->db->query("SELECT max(step_number) as max_step_number from testcase_ver_step WHERE testcase_ver_id=".$pair['testcase_ver_id']);
		if ($row = $res->fetch())
			$pair['step_number'] = $row['max_step_number'] + 1;
		else
			$pair['step_number'] = 1;
		return parent::_saveOne($db, $table, $pair);
	}
/*	
	protected function getInformationViewParams($params){
		$view_params = parent::getInformationViewParams($params);
		$view_params['tabs']['view_edit']['cols'] = 1;
		return $view_params;
	}
*/	
}
