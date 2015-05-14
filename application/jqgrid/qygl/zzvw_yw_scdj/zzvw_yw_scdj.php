<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//接收采购的货物
class qygl_zzvw_yw_scdj extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		$this->options['real_table'] = 'yw';
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'hb_id'=>array('label'=>'员工', 'data_source_table'=>'zzvw_yg'),
			// 'gx_id'=>array('label'=>'工序', 'from'=>'qygl.yw_scdj'),
			'zzvw_pici_scdj'=>array('label'=>'产品清单', 'formatter'=>'multi_row_edit', 'legend'=>'', 'from'=>'qygl.zzvw_pici_scdj',
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(wz_id)s原有%(amount)s, 现余%(remained)s')
			),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'登记人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_yg'),
			'happen_date'=>array('label'=>'生产日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['add'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_SC), 'hb_id', 'zzvw_pici_scdj', 
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_SC),'hb_id', 'zzvw_pici_scdj',
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2one'=>array(
				array('table'=>'yw_scdj', 'self_link_field'=>'yw_id')
				),
			'one2m'=>array(
				array('table'=>'zzvw_pici_scdj',
					'real_table'=>'pici',
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
