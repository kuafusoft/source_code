<?php
require_once('action_jqgrid.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/yw_tool.php');

class qygl_defect_gx_wz_action_get_defect_by_dingdan extends action_jqgrid{
	protected function handlePost(){
		$tool = new yw_tool($this->tool);
// print_r($this->params)		;
		$sql = "SELECT defect.id, defect.name from defect_gx_wz left join defect on defect_gx_wz.defect_id=defect.id".
			" left join dingdan on dingdan.wz_id=defect_gx_wz.wz_id".
			" WHERE dingdan.id={$this->params['value']} AND defect_gx_wz.gx_id=".GX_FL_CG;
		$res = $this->tool->query($sql);
		while($row = $res->fetch())
			$ret[] = $row;

		if(empty($ret))
			$ret[] = array('id'=>0, 'name'=>'没有缺陷');
		
		return $ret;
	}
}
