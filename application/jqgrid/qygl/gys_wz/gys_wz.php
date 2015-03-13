<?php
require_once('table_desc.php');
//员工管理
class qygl_gys_wz extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'hb_id'=>array('label'=>'名称', 'editable'=>false, 'formatter'=>'text'),
			'wz_id'=>array('label'=>'物资'),
        );
	}
}
