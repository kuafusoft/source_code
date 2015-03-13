<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_dingdan_action_get_dingdan_by_hb extends action_jqgrid{
	protected function handlePost(){
		$tool = new yw_tool($this->tool);
		$conditions = array('yw_fl_id'=>$this->params['yw_fl_id'], 'yw_isactive'=>ISACTIVE_ACTIVE, 'hb_id'=>$this->params['value']);
		if(!empty($this->params['dingdan_status_id']))
			$conditions['dingdan_status_id'] = $this->params['dingdan_status_id'];
// print_r($this->params)		;
		$ret = $tool->getDingdanOptions($conditions);//$this->params['yw_fl_id'], ISACTIVE_ACTIVE, $this->params['value']);
		return $ret;
	}
}
