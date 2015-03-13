<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_wz_action_get_wz_by_hb extends action_jqgrid{
	protected function handlePost(){
		$tool = new wz_tool($this->tool);
		$ret = $tool->getWZs($this->params['value'], $this->params['yw_fl_id']);
		return $ret;
	}
}
