<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");

class qygl_zzvw_yunshu extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
// print_r($dingdan_options);		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_id'=>array('label'=>'业务'),
			'dingdan_id'=>array('label'=>'订单'),
			'wz_id'=>array('label'=>'物资'),
			'dingdan_amount'=>array('label'=>'订单量', 'editable'=>false),
			'completed_amount'=>array('label'=>'已完成量', 'editable'=>false),
            'amount'=>array('label'=>'运输量'),
			'pici_id'=>array('label'=>'批次'),
			'dizhi'=>array('label'=>'地址'),
        );
		if($params['yw_fl_id'] == YW_FL_YUNRU){ //运入
			$this->options['edit'] = array('dingdan_id', 'amount');
		}
		else{ //运出
			$this->options['edit'] = array('dingdan_id', 'amount', 'pici_id', 'dizhi');
		}
		// $this->options['edit'] = array('wz_id', 'price', 'amount', 'fh_fl_id', 'next_date');
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		if($columnDef['name'] == 'dingdan_id'){
			$yw_tool = new yw_tool($this->tool);
			$dingdan_options = $yw_tool->getDingdanOptions($this->params['yw_fl_id']);
			$columnDef['editoptions']['value'] = $dingdan_options;
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
