<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//工序类型管理
class qygl_gx_fl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'name'=>array('label'=>'工序类型'),
			'note'=>array('label'=>'备注')
        );
		
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'gx_fl_id', 'db'=>'qygl', 'table'=>'gx');
	}
}
