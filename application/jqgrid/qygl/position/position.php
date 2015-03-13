<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//员工管理
class qygl_position extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            // 'ck_fl_id'=>array('label'=>'仓库类型'),
			'name'=>array('label'=>'职位名称'),
			'zhize'=>array('label'=>'职责范围'),
			'isactive'=>array('label'=>'是否有效'),
        );
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'position_id', 'db'=>'qygl', 'table'=>'hb_yg');
	}
}
