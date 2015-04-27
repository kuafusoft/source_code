<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/hb_tool.php");
//发货
class qygl_yw_fh extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		$hb_tool = new hb_tool($this->tool);
		// $year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);

		// $cyr_list = $hb_tool->getCYR(true, false, true);

        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'cyr_id'=>array('label'=>'承运人', 'data_source_table'=>'gys'),
			'yunshu_price'=>array('label'=>'运输单价', 'post'=>array('value'=>'元/吨')),
			'zxr_id'=>array('label'=>'装卸人', 'data_source_table'=>'yg'),
			'zx_price'=>array('label'=>'装卸单价', 'post'=>array('value'=>'元/吨')),
			'zzvw_xs_pici'=>array('label'=>'清单', 'editable'=>true, 'legend'=>'',
				'formatter'=>'multi_row_edit', 
				'formatoptions'=>array('subformat'=>'temp', 'temp'=>"%(kh_id)s订购的%(defect_id)s的%(wz_id)s %(amount)s")
				),
			'weight'=>array('label'=>'重量', 'post'=>array('value'=>'吨')),
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			'jbr_id'=>array('label'=>'发货人', 'data_source_db'=>'qygl', 'data_source_table'=>'yg'),
			'happen_date'=>array('label'=>'入库日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['add'] = array('cyr_id', 'yunshu_price', 'zxr_id', 'zx_price', 'zzvw_xs_pici', 'weight', 
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->options['edit'] = array('cyr_id', 'yunshu_price', 'zxr_id', 'zx_price', 'zzvw_xs_pici', 'weight', 
			'dj_id', 'note', 'jbr_id', 'happen_date');
		$this->linkTables = array(
			'one2m'=>array(
				'table'=>'zzvw_xs_pici',
				'real_table'=>'xs_pici'
			)
		);
	}
	
	protected function setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'yw_fh_id', 'db'=>'qygl', 'table'=>'zzvw_xs_pici');
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
