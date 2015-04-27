<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
//下采购单
class qygl_zzvw_yw_jd extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		$yw_tool = new yw_tool($this->tool);
		// $year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);

		$this->options['real_table'] = 'yw';
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'name'=>array('label'=>'概述'),
			'hb_id'=>array('label'=>'客户', 'editrules'=>array('required'=>true), 'data_source_table'=>'zzvw_kh'),
			'dingdan'=>array('label'=>'采购清单', 'editable'=>true, 'legend'=>'',
				// 'data_source_db'=>'qygl', 'data_source_table'=>'dingdan_cg', 'from'=>'qygl.dingdan_cg',
				'formatter'=>'multi_row_edit', 
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"%(defect_id)s的%(wz_id)s %(amount)s, 单价%(price)s 元")
				),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'接单人', 'data_source_db'=>'qygl', 'data_source_table'=>'yg'),
			'happen_date'=>array('label'=>'接单日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['add'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_JD), 'hb_id', 'dingdan', 
			'note', 'jbr_id', 'happen_date');
		$this->options['edit'] = array('yw_fl_id'=>array('type'=>'hidden', 'defval'=>YW_FL_JD), 'hb_id'=>array('editable'=>false), 'dingdan', 
			'note', 'jbr_id', 'happen_date');
			
		$this->linkTables = array(
			'one2m'=>array(
				'dingdan'=>array('table'=>'dingdan', 'self_link_field'=>'yw_id')
				)
		);
		$this->options['parent'] = array('table'=>'zzvw_kh', 'field'=>'hb_id');
	}
	
	protected function setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'yw_id', 'db'=>'qygl', 'table'=>'dingdan');
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
