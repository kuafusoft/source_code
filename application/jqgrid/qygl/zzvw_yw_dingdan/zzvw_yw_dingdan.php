<?php
require_once('table_desc.php');
//原料管理
class qygl_zzvw_yw_dingdan extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_fl_id'=>array('label'=>'分类', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'交易方'),
			'wz_id'=>array('label'=>'物资'),
			'amount'=>array('label'=>'数量', 'post'=>'吨'),
			'price'=>array('label'=>'单价', 'post'=>'元'),
			'fh_fl_id'=>array('label'=>'发货方式'),
			'next_date'=>array('label'=>'发货日期', 'stype'=>'select', 'searchoptions'=>array('value'=>$year_month1)), //只提供三年内的查询
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'经办人'),
			'happen_date'=>array('label'=>'下单日期', 'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'dj_id'=>array('label'=>'单据'),
			'completed_amount'=>array('label'=>'已完成量'),
			'dingdan_status_id'=>array('label'=>'状态'),
			'*'=>array('hidden'=>true),
        );

		$this->options['edit'] = array('yw_fl_id', 'hb_id', 'wz_id', 'remained'=>array('label'=>'当前库存', 'editable'=>false, 'post'=>'吨', 'DATA_TYPE'=>'float'),
			'amount', 'price', 
			'total_money'=>array('label'=>'总价', 'DATA_TYPE'=>'float', 'editable'=>false, 'post'=>'元'),
			'fh_fl_id', 'next_date', 'note'=>array('rows'=>2), 'dingdan_status_id', 'jbr_id', 'happen_date');
	}
	
	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['yw_fl_id'] = array(array('field'=>'id', 'op'=>'IN', 'value'=>array(1, 12)));
	}    

	// protected function contextMenu(){
		// $menu = array('ruku'=>'入库', 	// 入库
			// 'js'=>'结束', 				// 结束这个订单
			// 'qx'=>'取消'				// 取消该订单
		// );
		// return $menu;
    // }
	
	
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
			'js'=>array('caption'=>'结束', 'title'=>'结束订单'),
			'qx'=>array('caption'=>'取消', 'title'=>'取消订单'),
        );
        return array_merge($buttons, parent::getButtons());
    }
}
