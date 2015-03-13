<?php
require_once('table_desc.php');
//员工管理
class qygl_dingdan extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_id'=>array('label'=>'业务'),
			'hb_id'=>array('label'=>'交易方'),
			'wz_id'=>array('label'=>'物资'),
			'price'=>array('label'=>'单价'),
            'amount'=>array('label'=>'数量'),
            'completed_amount'=>array('label'=>'已完成量'),
			'next_date'=>array('label'=>'发货日期'),
			'dingdan_status_id'=>array('label'=>'状态')
        );
		$this->options['edit'] = array('wz_id', 'price', 'amount', 'next_date');
	}
	
	// public function accessMatrix(){
		// $access_matrix = array(
			// 'all'=>array('index'=>true, 'list'=>true, 'export'=>true),
			// 'admin'=>array('all'=>true, ),
		// );
		
		// $access_matrix['row_owner'] = $access_matrix['assistant_admin'] = $access_matrix['admin'];
		
		// return $access_matrix;
	// }
	
	public function getMoreInfoForRow($row){
		$res = $this->tool->query("SELECT hb_id FROM yw WHERE id={$row['yw_id']}");
		$hb = $res->fetch();
		$row['hb_id'] = $hb['hb_id'];
		return $row;
	}
	
    protected function getButtons(){
		$p_buttons = parent::getButtons();
		unset($p_buttons['add']);
		$buttons = array(
			'redo'=>array('caption'=>'再来一份'),
			'cancel'=>array('caption'=>'取消'),
			'finish'=>array('caption'=>'完成'),
        );
        return array_merge($buttons, $p_buttons);
    }
}
