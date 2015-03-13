<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_wz_action_get_wz_by_id extends action_jqgrid{
	protected function handlePost(){
		$ret = array();
		$tool = new wz_tool($this->tool);
		$conditions = array(array('field'=>'id', 'op'=>'in', 'value'=>$this->params['id']));
		$wzs = $tool->getWZ($conditions);
		foreach($wzs as $wz){
			$ret[$wz['id']] = $wz;
		}
		return json_encode($ret);
	}
}
