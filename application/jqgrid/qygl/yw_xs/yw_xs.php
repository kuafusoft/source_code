<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
//业务管理
class qygl_yw_xs extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		$yw_tool = new yw_tool($this->tool);
		// $year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);

        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'kh_id'=>array('label'=>'客户', 'editrules'=>array('required'=>true)),
			'dingdan_xs'=>array('label'=>'销售清单', 'editable'=>true, 'legend'=>'',
				// 'data_source_db'=>'qygl', 'data_source_table'=>'dingdan_cg', 'from'=>'qygl.dingdan_cg',
				'formatter'=>'multi_row_edit', 
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"%(defect_id)s的%(wz_id)s %(amount)s, 单价%(price)s 元 [%(dingdan_status_id)s]")
				),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'接单人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_hb_yg'),
			'happen_date'=>array('label'=>'接单日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['add'] = array('kh_id', 'dingdan_xs', 
			'note', 'jbr_id', 'happen_date');
		$this->options['edit'] = array('kh_id'=>array('editable'=>false), 'dingdan_xs', 
			'note', 'jbr_id', 'happen_date');
			
		$this->linkTables = array(
			'one2m'=>array('dingdan_xs')
		);
		$this->options['parent'] = array('table'=>'kh', 'field'=>'kh_id');
	}
	
	protected function setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'yw_xs_id', 'db'=>'qygl', 'table'=>'dingdan_xs');
	}
	
	
	// protected function _setSubGrid(){
        // $this->options['gridOptions']['subGrid'] = true;
		// $this->options['subGrid'] = array('expandField'=>'yw_xs_id', 'db'=>'qygl', 'table'=>'dingdan_xs');
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
			'jh'=>array('caption'=>'重新激活', 'title'=>'重新激活订单')
        );
        return array_merge($buttons, parent::getButtons());
    }
}
