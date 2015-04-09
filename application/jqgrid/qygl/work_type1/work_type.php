<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//工种管理
class qygl_work_type1 extends table_desc{
	protected function init($db, $table, $params = array()){
// print_r($params);
		parent::init($db, $table, $params);
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'工种', 'editrules'=>array('required'=>true)),
			'skill_ids'=>array('label'=>'技能')
        );
	}
}
