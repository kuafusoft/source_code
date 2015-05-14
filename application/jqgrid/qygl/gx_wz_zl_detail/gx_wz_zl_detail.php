<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
class qygl_gx_wz_zl_detail extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'gx_id'=>array('label'=>'工序'),
			'wz_id'=>array('label'=>'物资'),
			'zl_id'=>array('label'=>'质量等级'),
			'price'=>array('label'=>'单价'),
			'ck_weizhi_id'=>array('label'=>'位置'),
			'min_kc'=>array('label'=>'最低库存'),
			'max_kc'=>array('label'=>'最高库存'),
			'pd_days'=>array('label'=>'盘点周期')
        );
	}

	protected function handleFillOptionCondition(){
		$this->fillOptionConditions['gx_id'] = array(array('field'=>'gx_fl_id', 'op'=>'<>', 'value'=>GX_FL_FSCX));
	}
}
