<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//发货
class qygl_zzvw_yw_fh extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		// $year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
		//获取运输和装卸的默认单价
		$price = array(WZ_YUNSHU=>0, WZ_ZHUANGXIE=>0);
		$res = $this->tool->query("SELECT id, default_price FROM wz WHERE id in (".WZ_YUNSHU.','.WZ_ZHUANGXIE.")");
		while($row = $res->fetch())
			$price[$row['id']] = $row['default_price'];
		
		$this->options['real_table'] = 'yw';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'hb_id'=>array('label'=>'承运人', 'data_source_table'=>'zzvw_cyr'),
			'yunshu_price'=>array('label'=>'运输单价', 'post'=>array('value'=>'元/吨'), 'defval'=>$price[WZ_YUNSHU], 'from'=>'qygl.yw_yunshu', 'editable'=>true, 'DATA_TYPE'=>'float'),
			'zxr_id'=>array('label'=>'装卸人', 'data_source_table'=>'zzvw_zxr', 'from'=>'qygl.yw_yunshu', 'editable'=>true),
			'zx_price'=>array('label'=>'装卸单价', 'post'=>array('value'=>'元/吨'), 'defval'=>$price[WZ_ZHUANGXIE], 'from'=>'qygl.yw_yunshu', 'editable'=>true, 'DATA_TYPE'=>'float'),
			'zzvw_yw_fh_detail'=>array('label'=>'发运清单', 'editable'=>true, 'legend'=>'', 'required'=>true,
				'formatter'=>'multi_row_edit', 
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"%(hb_id)s订购的%(wz_id)s %(amount)s")
				),
			'weight'=>array('label'=>'重量', 'post'=>array('value'=>'吨'), 'from'=>'qygl.yw_yunshu', 'editable'=>true, 'DATA_TYPE'=>'float'),
			'kg_id'=>array('label'=>'库管', 'from'=>'qygl.yw_yunshu', 'data_source_table'=>'zzvw_yg', 'editable'=>true),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'发货人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_yg'),
			'happen_date'=>array('label'=>'出库日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['add'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_FH),'hb_id', 'yunshu_price', 'zxr_id', 'zx_price', 'zzvw_yw_fh_detail', 'weight', 
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_FH),'hb_id', 'yunshu_price', 'zxr_id', 'zx_price', 'zzvw_yw_fh_detail', 'weight', 
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2one'=>array(
				array('table'=>'yw_yunshu', 'self_link_field'=>'yw_id')
				),
			'one2m'=>array(
				array('table'=>'zzvw_yw_fh_detail',
					'real_table'=>'yw_fh_detail',
					'self_link_field'=>'yw_id'
				)
			)
		);
	}
	
	protected function setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'yw_id', 'db'=>'qygl', 'table'=>'zzvw_pici_sh');
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
			'js'=>array('caption'=>'结束', 'title'=>'结束订单'),
			'jh'=>array('caption'=>'重新激活', 'title'=>'重新激活订单')
        );
        return array_merge($buttons, parent::getButtons());
    }
}
