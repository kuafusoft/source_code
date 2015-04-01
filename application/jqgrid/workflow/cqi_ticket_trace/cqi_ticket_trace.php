<?php
require_once('table_desc.php');

class workflow_cqi_ticket_trace extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id',
			'cqi_ticket_id', 
			'creater_id'=>array('editable'=>true),
			'update_date'=>array('label'=>'Update On', 'defval'=>date('Y-m-d')),
			'content',
			'*'
		);
		$this->options['edit'] = array('cqi_ticket_id', 'creater_id', 'update_date', 'content');
    } 
}
