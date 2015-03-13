<?php
require_once('table_desc.php');
require_once('const_def_qygl.php');
//技巧等级管理
class qygl_defect extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		
        $this->options['list'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'缺陷', 'editrules'=>array('required'=>true)),
			'zl_id'=>array('label'=>'质量等级'),
			'description'=>array('label'=>'描述'),
			'defect_gx_wz'=>array('label'=>'可能出现的场景', 'legend'=>'缺陷场景', 'formatter'=>'multi_row_edit', 'data_source_table'=>'defect_gx_wz')
        );
		$this->linkTables = array(
			'defect_gx_wz'=>array('link_table'=>'defect_gx_wz', 'self_link_field'=>'defect_id'),
			);

		$this->parent_table = 'zl';
		$this->parent_field = 'zl_id';
	}
}
