<?php
require_once('table_desc.php');

class xt_codec_stream_ver extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['list'] = array(
			'id'=>array('editable'=>false, 'hidden'=>true, 'formatter'=>'infoLink'),
			'ver'=>array('label'=>'Version', 'editable'=>false, 'formatter'=>'updateViewEditPage'),
			'steps',
			'precondition'=>array('label'=>'Pre-conditions'),
			'expected_result'=>array('label'=>'Expected Result'),
			'testcase_priority_id'=>array('label'=>'Priority'),
			'prj_ids'=>array('from'=>'xt.prj', 'label'=>'Projects', 'editable'=>true, 'type'=>'cart'),
			'testcase_ids'=>array('from'=>'xt.testcase', 'label'=>'Testcase', 'editable'=>true, 'type'=>'cart'),
			'update_comment'=>array('label'=>'Update Comment', 'editable'=>false),
			'review_comment'=>array('label'=>'Review Comment', 'editable'=>false),
			'edit_status_id'=>array('label'=>'Edit Status', 'editable'=>false),
			'owner_id'=>array('label'=>'Owner'),
			'updated'=>array('label'=>'Updated')
		);
		$this->options['gridOptions']['label'] = 'Codec Stream';
		$this->options['navOptions']['refresh'] = false;
        $this->options['ver'] = '1.0';
		
		$this->options['add'] = $this->options['list'];
		unset($this->options['add']['updated']);
// print_r($this->options['add'])		;
		$this->linkTables = array(
			'node_ver_m2m'=>array('prj', 'testcase')
		);
    } 
}
