<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//入库管理

class qygl_chuku extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['edit'] = array('hb_id'=>array('label'=>'客户'), 'dingdan_id'=>array('label'=>'订单'), 'pici_detail_id'=>array('label'=>'批次'), 'amount'=>array('label'=>'数量'));
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$hb_tool = new hb_tool($this->tool);
		if($columnDef['name'] == 'dingdan_id'){
			$yw_tool = new yw_tool($this->tool);
			$dingdan_options = $yw_tool->getDingdanOptions(array('yw_fl_id'=>$this->params['yw_fl_id']));
			$columnDef['editoptions']['value'] = $dingdan_options;
		}
		else if($columnDef['name'] == 'hb_id'){
			$columnDef['editoptions']['value'] = $hb_tool->getKH(0, true, false, true); //客户
		}
		else{
			parent::fillOptions($columnDef, $db, $table);
		}
	}
}
