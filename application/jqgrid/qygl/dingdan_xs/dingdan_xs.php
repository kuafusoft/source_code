<?php
require_once('table_desc.php');
require_once("const_def_qygl.php");
require_once(APPLICATION_PATH.'/jqgrid/qygl/wz_tool.php');

class qygl_dingdan_xs extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);		
		parent::init($db, $table, $params);
		$info = array('unit_name'=>'?');
		if($this->actionName == 'information' || $this->actionName == 'update_information_page'){
			$sql = "SELECT unit.name as unit_name from wz left join unit on wz.unit_id=unit.id left join dingdan_xs on dingdan_xs.wz_id=wz.id where dingdan_xs.id={$params['id']}";
			$res = $this->tool->query($sql);
			$info = $res->fetch();
		}

        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_xs_id'=>array('label'=>'业务', 'hidden'=>true, 'hidedlg'=>true),
			'wz_id'=>array('label'=>'物资'),
			'unit_name'=>array('label'=>'计量单位', 'hidden'=>true, 'hidedlg'=>true),
			'defect_id'=>array('label'=>'质量'),
			'price'=>array('label'=>'单价', 'post'=>array('type'=>'text', 'value'=>'元/'.$info['unit_name'])),
            'amount'=>array('label'=>'数量', 'post'=>array('type'=>'text', 'value'=>$info['unit_name'])),
            'completed_amount'=>array('label'=>'已完成量'),
			'dingdan_status_id'=>array('label'=>'状态')
        );
		$this->options['edit'] = array('wz_id', 'unit_name'=>array('type'=>'hidden'), 'defect_id'=>array('editable'=>true), 'price', 'amount', 'dingdan_status_id'=>array('type'=>'hidden'));
	}
	
	protected function setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'dingdan_xs_id', 'db'=>'qygl', 'table'=>'dingdan_xs_jfjh');
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
		$p_buttons = parent::getButtons();
		unset($p_buttons['add']);
		$buttons = array(
			'jh'=>array('caption'=>'重启'),
			'js'=>array('caption'=>'完成'),
        );
        return array_merge($buttons, $p_buttons);
    }
}
