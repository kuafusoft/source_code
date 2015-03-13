<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");

class qygl_wz_action_getdefectpriceweizhibygxwz extends action_jqgrid{
	protected function handlePost(){
		$ret = array('defect'=>array(), 'price'=>array(), 'ck_weizhi'=>array());
// print_r($this->params);	
		$res = $this->tool->query("SELECT defect.id, defect.name FROM defect_gx_wz left join defect on defect.id=defect_gx_wz.defect_id WHERE gx_id={$this->params['gx']} and (wz_id={$this->params['wz']} or wz_id=0)");
		while($row = $res->fetch())
			$ret['defect'][] = $row;
		if(empty($ret['defect']))
			$ret['defect'][] = array("id"=>1, 'name'=>'正品，无缺陷');
		return $ret;
	}
}
