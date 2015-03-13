<?php
require_once('table_desc.php');
//原料管理
class qygl_zzvw_wz_yl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
			'wz_from_id'=>array('label'=>'来源'),
			'default_price'=>array('label'=>'默认单价', 'post'=>'元'),
			'midu'=>array('label'=>'密度', 'hidden'=>true, 'post'=>'g/cm^3'),
			'unit_id'=>array('label'=>'计量单位'),
			'min_kc'=>array('label'=>'最小库存'),
			'max_kc'=>array('label'=>'最大库存'),
			'ck_id'=>array('label'=>'默认仓库', 'hidden'=>true),
			'remained'=>array('label'=>'当前库存', 'editable'=>false),
			'pd_days'=>array('label'=>'盘点周期', 'hidden'=>true, 'post'=>'天'),
			'pd_last'=>array('label'=>'末次盘点日期', 'hidden'=>true),
			'jy_days'=>array('label'=>'最大积压', 'hidden'=>true, 'post'=>'天'),
			'pic'=>array('label'=>'照片', 'hidden'=>true),
			'note'=>array('label'=>'备注', 'hidden'=>true),
			'isactive'=>array('label'=>'状态', 'editable'=>false, 'hidden'=>true),
        );

		$this->options['edit'] = array('wz_from_id', 'name', 'default_price', 'midu', 
			'unit_fl_id'=>array('label'=>'计量类型'), 'unit_id', 'min_kc', 'max_kc', 'ck_id', 
			'pd_days', 'pd_last', 'jy_days', 'pic', 'note');
		// $this->options['navOptions']['refresh'] = false;
	}
	
    protected function contextMenu(){
		$menu = array('cg'=>'采购', 	// 更改remained，并记录到相关业务表
			'pd'=>'盘点', 				// 更改remained，并记录到相关业务表
			'th'=>'退货'				// 更改remained，并记录到相关业务表
		);
		return $menu;
    }
	
	
	// public function accessMatrix(){
		// $access_matrix = array(
			// 'all'=>array('index'=>true, 'list'=>true, 'export'=>true),
			// 'admin'=>array('all'=>true, ),
		// );
		
		// $access_matrix['row_owner'] = $access_matrix['assistant_admin'] = $access_matrix['admin'];
		
		// return $access_matrix;
	// }
	
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
