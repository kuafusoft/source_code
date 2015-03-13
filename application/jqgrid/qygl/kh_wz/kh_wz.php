<?php
require_once('table_desc.php');
//客户需求的物资
class qygl_kh_wz extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'hb_id'=>array('label'=>'名称', 'editable'=>false, 'formatter'=>'int'),
			'wz_id'=>array('label'=>'物资'),
        );
	}
}
