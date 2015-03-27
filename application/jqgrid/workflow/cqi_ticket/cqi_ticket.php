<?php
require_once('table_desc.php');

class workflow_cqi_ticket extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		//给module添加searchoptions
		$soptions = array(0=>' ');
		$res = $this->db->query("SELECT * FROM module");
		while($row = $res->fetch()){
			$soptions[$row['id']] = $row['name'];
		}
		
		//user list
		$userList = array(0=>'');
		$userAdmin = new Application_Model_Useradmin(null);
		foreach($userAdmin->getUserList() as $k=>$n)
			$userList[$k] = $n;
// print_r($userList);		
		$this->options['list'] = array(
			'id',
			'manager_id'=>array('label'=>'Team'),
			'open_date'=>array('label'=>'Open Date'),
			'close_date'=>array('label'=>'Close Date', 'hidden'=>true),
			'effort'=>array('label'=>'Effort(Man-Days)'),
			'customer_id',
			'prj_id'=>array('label'=>'Project'),
			'content',
			'ticket_status_id'=>array('label'=>'Status'),
			'root_cause_id'=>array('label'=>'Root Cause'),
			'solution_id'=>array('hidden'=>true),
			'cqi_ticket_module'=>array('label'=>'Module', 'editable'=>true, 'data_source_db'=>'workflow', 
				'search'=>true, 'stype'=>'select', 'searchoptions'=>array('value'=>$this->tool->array2str($soptions)), 
				'data_source_table'=>'cqi_ticket_module', 'from'=>'workflow.cqi_ticket_module',
				'formatter'=>'multi_row_edit', 'formatoptions'=>array('subformat'=>'temp', 'temp'=>'Module[%(module_id)s] has %(question_type_id)s question'), 'legend'=>'Modules'),
			'cqi_ticket_trace'=>array('label'=>'Progress Record', 'data_source_table'=>'cqi_ticket_trace', 'data_source_db'=>'workflow', 'from'=>'workflow.cqi_ticket_trace', 
				'search'=>false, 
				'formatter'=>'multi_row_edit', 'formatoptions'=>array('subformat'=>'temp', 'temp'=>'[%(creater_id)s] updated on [%(update_date)s]: <BR />%(content)s'), 'legend'=>'Records'),
			'hw_ae_id'=>array('hidden'=>true, 'label'=>'HW AE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'sw_ae_id'=>array('hidden'=>true, 'label'=>'SW AE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'pe_id'=>array('hidden'=>true, 'label'=>'PE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'te_id'=>array('hidden'=>true, 'label'=>'TE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'fqe_id'=>array('hidden'=>true, 'label'=>'FQE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'cqe_id'=>array('hidden'=>true, 'label'=>'CQE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'fae_id'=>array('hidden'=>true, 'label'=>'FAE', 
				'formatter'=>'select', 'formatoptions'=>array('value'=>$userList), 
				'edittype'=>'select', 'editoptions'=>array('value'=>$userList),
				'addoptions'=>array('value'=>$userList)
				),
			'*'=>array('hidden'=>true)
		);
		$this->options['edit'] = array('input_source_id'=>array('editable'=>false), 'input_person'=>array('editable'=>false), 
			'customer_id'=>array('editable'=>false), 'prj_id'=>array('editable'=>false), 
			'ticket_status_id'=>array('editable'=>true), 'cqi_ticket_module', 'content', 
			'community_thread_number', 'community_link', 'linked_ct_number', 'root_cause_id', 'close_date', 
			'hw_ae_id', 'sw_ae_id', 'soc_ae_id', 'pe_id', 'te_id', 'fqe_id', 'cqe_id', 'fae_id', 'point_of_failure',
			'cqi_ticket_trace'
			);
		$this->options['add'] = array('input_source_id', 'input_person', 'customer_id', 'prj_id'=>array('editable'=>true), 'ticket_status_id', 'cqi_ticket_module', 'content', 
			'community_thread_number', 'community_link', 'Linked_ct_number', 'root_cause_id', 'open_date',
			'hw_ae_id', 'sw_ae_id', 'soc_ae_id', 'pe_id', 'te_id', 'fqe_id', 'cqe_id', 'fae_id', 'point_of_failure', 
			'cqi_ticket_trace');
		
		$this->linkTables = array(
			'one2m'=>array(
				'cqi_ticket_trace'=>array('link_table'=>'cqi_ticket_trace', 'self_link_field'=>'cqi_ticket_id'),
				'cqi_ticket_module'=>array('link_table'=>'cqi_ticket_module', 'self_link_field'=>'cqi_ticket_idticket_id')
			)
		);
    } 
}
