<?php
require_once('tree_table_desc.php');

class workflow_period extends tree_table_desc{
	protected function getButtons(){
        $buttons = array(
			'add'=>array(),
			'batch_gen'=>array('caption'=>'Batch Generate'),
			'export'=>array('caption'=>'Export')
        );
//        $buttons = array_merge($buttons, parent::getButtons());
		return $buttons;
	}
}
