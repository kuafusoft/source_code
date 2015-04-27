<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
require_once(APPLICATION_PATH."/jqgrid/qygl/yw_tool.php");
//业务管理
class qygl_yw extends table_desc{
	protected $yw_fl_options = array();
	protected $hb_options = array();
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
		$yw_tool = new yw_tool($this->tool);
		// $year_month1 = $this->tool->getYearMonthList(6, 36, true);
		$year_month2 = $this->tool->getYearMonthList(0, 36, true);

		$data_source_db = 'qygl';
		$data_source_table = 'dingdan';
		$legend = '详情';
		$itemParams = array('id'=>0, 'yw_fl_id'=>0);
		if(!empty($params['id'])){
			$itemParams['id'] = $params['id'];
			$res = $this->tool->query("select * from yw where id={$params['id']} limit 1");
			if($row = $res->fetch()){
				$ret = $yw_tool->getDetailTable($row['yw_fl_id']);
				$data_source_db = $ret['data_source_db'];
				$data_source_table = $ret['data_source_table'];
				$legend = $ret['legend'];
				$itemParams['yw_fl_id'] = $row['yw_fl_id'];
			}
		}
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'name',
            'yw_fl_id'=>array('label'=>'分类', 'editrules'=>array('required'=>true)),
			'hb_id'=>array('label'=>'交易方', 'editrules'=>array('required'=>true)),
			
			'detail_id'=>array('label'=>'详情', 'formatter'=>'multi_row_edit','legend'=>$legend, 
				'formatoptions'=>array('subformat'=>'itemTemp', 'field'=>'yw_fl_id', 'temp'=>array()),
				'data_source_db'=>$data_source_db, 'data_source_table'=>$data_source_table, 'itemParams'=>$itemParams),
			'cyr_id'=>array('label'=>'承运人', 'data_source_db'=>'qygl', 'data_source_table'=>'hb', 'from'=>'qygl.yw_help'),
			'yunshu_price'=>array('label'=>'运输单价', 'from'=>'qygl.yw_help'),
			'zxr_id'=>array('label'=>'装卸人', 'data_source_db'=>'qygl', 'data_source_table'=>'hb', 'from'=>'qygl.yw_help'),
			'zx_price'=>array('label'=>'装卸单价', 'from'=>'qygl.yw_help'),
			'amount'=>array('label'=>'数量', 'post'=>'吨', 'hidden'=>true, 'from'=>'qygl.yw_help'),
			
			'dj_id'=>array('label'=>'单据'),
			'note'=>array('label'=>'备注'),
			// 'helper_id'=>array('label'=>'辅助人', 'data_source_table'=>'hb'), //主要是和运输相对应的装卸工
			// 'price'=>array('label'=>'单价', 'post'=>'元/吨', 'hidden'=>true),
			// 'helper_price'=>array('label'=>'辅助单价', 'post'=>'元/吨', 'hidden'=>true), //主要是和运输相对应的装卸单价
			'jbr_id'=>array('label'=>'经办人', 'data_source_db'=>'qygl', 'data_source_table'=>'zzvw_hb_yg'),
			'happen_date'=>array('label'=>'交易日期', 'edittype'=>'date',
				'stype'=>'select', 'searchoptions'=>array('value'=>$year_month2)), //只提供三年内的查询
			'*'=>array('hidden'=>true),
        );

		$this->options['edit'] = array('yw_fl_id', 'hb_id', 'detail_id', 
			'cyr_id', 'yunshu_price', 'zxr_id', 'zx_price', 'amount',
		// 'helper_id', 'price', 'helper_price',  
			'note', 'jbr_id', 'happen_date');
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
	
    // protected function getButtons(){
		//根据不同的业务类型，生成不同的button
        // $buttons = array(
			// 'js'=>array('caption'=>'结束', 'title'=>'结束订单'),
			// 'qx'=>array('caption'=>'取消', 'title'=>'取消订单'),
        // );
        // return array_merge($buttons, parent::getButtons());
    // }
}
