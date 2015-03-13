<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//员工管理
class qygl_ck extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            // 'ck_fl_id'=>array('label'=>'仓库类型'),
			'name'=>array('label'=>'仓库名称'),
			'address'=>array('label'=>'地址'),
			'volumn'=>array('label'=>'库容'),
            'kuguan_id'=>array('label'=>'库管', 'data_source_table'=>'hb'),
        );
	}

	protected function handleFillOptionCondition(){
		$yg = array();
		$res = $this->tool->query("SELECT * FROM hb_hb_fl WHERE hb_fl_id=".HB_FL_YG);
		while($row = $res->fetch())
			$yg[] = $row['hb_id'];
		$yg_ids = implode(',', $yg);
		$this->fillOptionConditions['kuguan_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$yg_ids));
	}
}
