<?php

require_once('table_desc.php');

class xt_testcase_module extends table_desc{
	protected $testcase_type_ids = '';
	protected $testcase_module_ids = '';
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
//		$this->options['linktype'] = 'infoLink_ver';
		$this->options['list'] = array('id'=>array('hidden'=>true), 
			'id', 
			'name',
			'description',
			'testcase_type_id',
			'creater_id'=>array('hidden'=>true),
			'isactive'=>array('editable'=>false),
			'cases'=>array('excluded'=>true, 'label'=>'Cases', 'search'=>false, 'editable'=>false)
		);
		// $this->options['query'] = array('normal'=>array('key', 'os_id', 'board_type_id', 'chip_id', 'prj_id'=>array('label'=>'Project'), 
			// 'testcase_type_id', 'testcase_category_id', 'testcase_module_id', 'owner_id'), 
			// 'advanced'=>array('testcase_source_id', 'testcase_priority_id', 'auto_level_id', 'edit_status_id', 'last_run', 'active'));
		$this->options['edit'] = array('name', 'description', 'testcase_type_ids', 'isactive');
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'testcase_module_id', 'db'=>'xt', 'table'=>'testcase_testpoint');
        $this->options['ver'] = '1.0';
		
		$this->linkTables = array('m2m'=>array('testcase_type'), 'history'=>array('testcase_module_history'));
    }

	protected function _getLimit($params){
		$t = tableDescFactory::get('xt', 'testcase_type', array());
		$t_limit = $t->getLimit();
		if($t_limit !== false){
			$limit = array();
			if(!empty($t_limit))
				$cond = implode(',', $t->getLimit());
			else
				$cond = '0';
			$res = $this->tool->query("SELECT testcase_module_id FROM testcase_module_testcase_type WHERE testcase_type_id in ($cond)");
			while($row = $res->fetch())
				$limit[] = $row['testcase_module_id'];
		}
		else
			$limit = false;
		return $limit;
	}
}
