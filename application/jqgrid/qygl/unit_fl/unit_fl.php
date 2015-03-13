<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');

class qygl_unit_fl extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
// print_r($params);		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称'),
			'unit_id'=>array('label'=>'标准单位'),
        );
	}
	
	protected function handleFillOptionCondition(){
		if(!empty($this->params['id'])){
			$this->fillOptionConditions['unit_id'] = array(array('field'=>'unit_fl_id', 'op'=>'=', 'value'=>$this->params['id']));
		}
	}
	
	
}
