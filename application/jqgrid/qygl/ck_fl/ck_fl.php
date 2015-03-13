<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//仓库类型管理
class qygl_ck_fl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'name'=>array('label'=>'类型名称'),
			'wz_fl_id'=>array('label'=>'物资类型'),
			'note'=>array('label'=>'备注'),
        );
	}
}
