<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//部门管理
class qygl_dept extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
			'name'=>array('label'=>'部门名称'),
			'note'=>array('label'=>'备注'),
        );
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'dept_id', 'db'=>'qygl', 'table'=>'hb_yg');
	}
}
