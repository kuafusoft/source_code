<?php
require_once('action_jqgrid.php');

class action_ver_action extends action_jqgrid{
	protected $ver_table = '';
	protected $link_table = '';
	protected $link_field = '';
	protected $history_table = '';
	
	protected function init(&$controller){
		parent::init($controller);
	}
	
	public function setParams($params){
		parent::setParams($params);
		$this->setHelpTable();
	}
	
	protected function setHelpTable($helpTable = array()){
		if (empty($helpTable['verTable']))
			$helpTable['verTable'] = $this->get('table').'_ver';
		if (empty($helpTable['linkTable']))
			$helpTable['linkTable'] = 'prj_'.$helpTable['verTable'];
		if (empty($helpTable['linkField']))
			$helpTable['linkField'] = 'prj_id'; //$helpTable['verTable'].'_id';
		if (empty($helpTable['historyTable']))
			$helpTable['historyTable'] = $helpTable['linkTable'].'_history';
// print_r($helpTable);			
		$this->ver_table = $helpTable['verTable'];
		$this->link_table = $helpTable['linkTable'];
		$this->link_field = $helpTable['linkField'];
		$this->history_table = $helpTable['historyTable'];
	}
}
?>