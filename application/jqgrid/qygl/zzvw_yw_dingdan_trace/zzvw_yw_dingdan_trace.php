<?php
require_once('table_desc.php');
//原料管理
class qygl_zzvw_yw_dingdan_trace extends table_desc{
	protected $dingdan_info = array();
	protected $wz_info = array();
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$res = $this->tool->query("SELECT * FROM zzvw_yw_dingdan WHERE id={$params['parent']}");
		$this->dingdan_info = $res->fetch();
		$res = $this->tool->query("SELECT wz.*, unit.name as unit FROM wz left join unit on wz.unit_id=unit.id WHERE id={$this->dingdan_info['wz']}");
		$this->wz_info = $res->fetch();
print_r($this->dingdan_info);
print_r($this->wz_info);
		$year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
print_r($params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'yw_fl_id'=>array('label'=>'业务', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'交易方'),
			'wz_id'=>array('label'=>'物资', 'editable'=>false, 'defval'=>$this->dingdan_info['wz_id']),
			'amount'=>array('label'=>'数量', 'post'=>$this->wz_info['unit']),
			'price'=>array('label'=>'单价', 'post'=>'元'),
			'pici_id'=>array('label'=>'批次'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'经办人'),
			'happen_date'=>array('label'=>'业务日期', 'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'dj_id'=>array('label'=>'单据'),
			'*'=>array('hidden'=>true),
        );
		$this->options['edit'] = array('yw_fl_id', 'hb_id', 'wz_id', 
			'dingdan_amount'=>array('label'=>'订单总量', 'editable'=>false, 'post'), 
			'dingdan_remained', 
			'remained'=>array('label'=>'当前库存', 'editable'=>false, 'post'=>'吨', 'DATA_TYPE'=>'float'),
			'amount', 'price', 
			'total_money'=>array('label'=>'总价', 'DATA_TYPE'=>'float', 'editable'=>false, 'post'=>'元'),
			'fh_fl_id', 'next_date', 'note'=>array('rows'=>2), 'dingdan_status_id', 'jbr_id', 'happen_date');
	}
	
	protected function handleFillOptionCondition(){
		
print_r($this->params);
		// $this->fillOptionConditions['yw_fl_id'] = array(array('field'=>'id', 'op'=>'IN', 'value'=>array(1, 12)));
		
	}    

}
