<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//物资管理
class qygl_wz extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);

		// print_r($params);
		$post = '?';
		$wz_fl_id = 0;
		if(!empty($params['id']) && count($params['id']) == 1){
			$res = $this->tool->query("select unit.name as unit, wz.wz_fl_id from wz left join unit on wz.unit_id=unit.id where wz.id={$params['id']}");
			$row = $res->fetch();
			$post = $row['unit'];
			if(empty($post))
				$post = '?';
			
			$wz_fl_id = $row['wz_fl_id'];
		}
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'物资', 'editrules'=>array('required'=>true)),
			'wz_fl_id'=>array('label'=>'类型', 'editrules'=>array('required'=>true)),
			'unit_id'=>array('label'=>'计量单位'),
			'default_price'=>array('label'=>'默认单价', 'post'=>'元'),
			'min_kc'=>array('label'=>'最低库存', 'post'=>$post),
			'max_kc'=>array('label'=>'最高库存', 'post'=>$post),
			'remained'=>array('label'=>'库存量', 'post'=>$post),
			'pd_days'=>array('label'=>'盘点周期', 'post'=>'天', 'hidden'=>true),
			'pd_last'=>array('label'=>'最近盘点日期', 'hidden'=>true),
			'jy_days'=>array('label'=>'最大积压天数', 'post'=>'天', 'hidden'=>true),
			'wh_days'=>array('label'=>'维护周期', 'post'=>'天', 'hidden'=>true),
			'midu'=>array('label'=>'密度', 'post'=>'克/立方厘米', 'hidden'=>true),
			'tj'=>array('label'=>'体积', 'post'=>'立方厘米', 'hidden'=>true),
			'bmj'=>array('label'=>'表面积', 'post'=>'平方厘米', 'hidden'=>true),
			'zuhe'=>array('label'=>'是否组合', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>array(1=>'单个零件', 2=>'组合产品')), 
				'stype'=>'select', 'searchoptions'=>array('value'=>array(0=>'', 1=>'单个零件', 2=>'组合产品')), 
				'edittype'=>'radio', 'editoptions'=>array('value'=>array(1=>'单个零件', 2=>'组合产品'))
			),
			'wz_cp_zuhe'=>array('from'=>'wz_cp_zuhe', 'label'=>'组合情况', 'legend'=>'零部件组合', 'data_source_table'=>'wz_cp_zuhe', 'formatter'=>'multi_row_edit', 'hidden'=>true, 'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(input_wz_id)s: %(amount)s')),
			'jszb_wz'=>array('from'=>'jszb_wz', 'label'=>'技术指标', 'legend'=>'详细技术指标要求', 'data_source_table'=>'jszb_wz', 'formatter'=>'multi_row_edit', 'hidden'=>true, 'formatoptions'=>array('subformat'=>'temp', 'temp'=>'%(jszb_id)s:[%(min_value)s, %(max_value)s]')),
			'defect_gx_wz'=>array('from'=>'defect_gx_wz', 'label'=>'缺陷', 'legend'=>'可能出现的缺陷', 'data_source_table'=>'defect_gx_wz', 'formatter'=>'multi_row_edit', 'hidden'=>true),
			'gx_wz_zl_detail'=>array('label'=>'单价等信息', 'legend'=>'默认的单价，存放位置等', 'data_source_table'=>'gx_wz_zl_detail', 'formatter'=>'multi_row_edit', 'hidden'=>true),
			'gys_id'=>array('label'=>'供应商', 'editable'=>true, 'data_source_table'=>'hb'),
			'kh_id'=>array('label'=>'客户', 'editable'=>true, 'data_source_table'=>'hb'),
			'pic'=>array('label'=>'照片', 'hidden'=>true),
			'note'=>array('label'=>'备注', 'hidden'=>true),
			'isactive'
        );
		$this->linkTables = array(
			'm2m'=>array(
				'gys'=>array('link_table'=>'gys_wz', 'self_link_field'=>'wz_id', 'link_field'=>'hb_id', 'refer_table'=>'hb'),
				'kh'=>array('link_table'=>'kh_wz', 'self_link_field'=>'wz_id', 'link_field'=>'hb_id', 'refer_table'=>'hb'),
				),
			'one2m'=>array(
				'jszb_wz'=>array('link_table'=>'jszb_wz', 'self_link_field'=>'wz_id'),
				'wz_cp_zuhe'=>array('link_table'=>'wz_cp_zuhe', 'self_link_field'=>'wz_id'),
				'gx_wz_zl_detail'=>array('link_table'=>'gx_wz_zl_detail', 'self_link_field'=>'wz_id'),
				'defect_gx_wz'=>array('link_table'=>'defect_gx_wz', 'self_link_field'=>'wz_id'),
				),
			);
		
		$this->options['edit'] = array('wz_fl_id', 'name', 'unit_fl_id'=>array('label'=>'计量单位类型'), 
			'unit_id', 'default_price', 
			'min_kc', 'max_kc', 'pd_days', 'pd_last', 'jy_days', 'wh_days',
			'midu', 'tj', 'bmj', 'zuhe', 'wz_cp_zuhe', 'jszb_wz', 'defect_gx_wz', 'gx_wz_zl_detail', 'gys_id', 'kh_id', 'pic', 'note', 'isactive'
		);
		// $this->options['navOptions']['refresh'] = false;
	}
	
	protected function contextMenu(){
		$menu = array(
			'cg'=>'采购',
			'xs'=>'接订单',
			'scdj'=>'生产登记',
		);
		return $menu;
	}
	
    // protected function getButtons(){
        // $buttons = array(
			// 'ask2review'=>array('caption'=>'申请审核'),
			// 'sign'=>array('caption'=>'正式签署'),
			// 'change'=>array('caption'=>'统一调整'), //统一调整工种、职位、基本工资、提成比例等
            // // 'scdj'=>array('caption'=>'生产登记'),
			// 'jsgz'=>array('caption'=>'生成工资单'),
            // // 'gz'=>array('caption'=>'发工资'),
        // );
        // return array_merge($buttons, parent::getButtons());
    // }
}
