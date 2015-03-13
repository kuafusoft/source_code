<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');

class qygl_wz_cp_zuhe extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
// print_r($params);	
		$input_limit = array();
		$res = $this->tool->query("SELECT id FROM wz WHERE wz_fl_id IN (".WZ_FL_CHANPIN.")");
		while($row = $res->fetch())
			$input_limit = $row['id'];
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'wz_id'=>array('label'=>'物资', 'formatter'=>'int'),
			// 'input_gx_id'=>array('label'=>'工序', 'data_source_table'=>'gx'),
			'input_wz_id'=>array('label'=>'零件', 'data_source_table'=>'wz', 'limit'=>$input_limit),
			'amount'=>array('label'=>'数量'),
        );
	}
}
