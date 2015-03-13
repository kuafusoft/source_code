<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//出入库管理

class qygl_yw_ck extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
// print_r($params);		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_id'=>array('label'=>'业务'),
			'dingdan_id'=>array('label'=>'订单'),
			'zhuangxie_id'=>array('label'=>'装卸'),
			'yunshu_id'=>array('label'=>'运输'),
            'amount'=>array('label'=>'数量', 'post'=>'?'),
			'pici_id'=>array('label'=>'批次'),
        );
		if($params['yw_fl_id'] == YW_FL_RUKU){ //入库
			$this->options['edit'] = array('hb_id'=>array('label'=>'供应商'), 'dingdan_id', 'defect_id'=>array('label'=>'缺陷'), 'amount', 'in_ck_weizhi_id'=>array('label'=>'仓位', 'data_source_table'=>'ck_weizhi'));
		}
		else{ //出库
			$this->options['edit'] = array('hb_id'=>array('label'=>'客户'), 'dingdan_id', 'pici_id', 'amount');
		}
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$hb_tool = new hb_tool($this->tool);
		if($columnDef['name'] == 'dingdan_id'){
			$yw_tool = new yw_tool($this->tool);
			$dingdan_options = $yw_tool->getDingdanOptions(array('yw_fl_id'=>$this->params['yw_fl_id']));
			$columnDef['editoptions']['value'] = $dingdan_options;
		}
		else if($columnDef['name'] == 'hb_id'){
			if($this->params['yw_fl_id'] == YW_FL_RUKU)
				$columnDef['editoptions']['value'] = $hb_tool->getSTGYS(0, true, false, true); //实体供应商
			else
				$columnDef['editoptions']['value'] = $hb_tool->getKH(0, true, false, true);
		}
		else{
			parent::fillOptions($columnDef, $db, $table);
		}
	}
	
	// public function accessMatrix(){
		// $access_matrix = array(
			// 'all'=>array('index'=>true, 'list'=>true, 'export'=>true),
			// 'admin'=>array('all'=>true, ),
		// );
		
		// $access_matrix['row_owner'] = $access_matrix['assistant_admin'] = $access_matrix['admin'];
		
		// return $access_matrix;
	// }
	
    protected function getButtons(){
        $buttons = array(
			'ask2review'=>array('caption'=>'申请审核'),
			'sign'=>array('caption'=>'正式签署'),
			'change'=>array('caption'=>'统一调整'), //统一调整工种、职位、基本工资、提成比例等
            // 'scdj'=>array('caption'=>'生产登记'),
			'jsgz'=>array('caption'=>'生成工资单'),
            // 'gz'=>array('caption'=>'发工资'),
        );
        return array_merge($buttons, parent::getButtons());
    }
}
