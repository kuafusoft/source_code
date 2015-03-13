<?php

require_once('table_desc.php');

class xt_rel extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name',
			'os_ids'=>array('hidden'=>true, 'label'=>'OS', 'editable'=>true),
			'rel_category_id'=>array('label'=>'Category'),
			'*'=>array('hidden'=>true, 'editable'=>false)
		);
		$this->options['gridOptions']['label'] = 'Release';
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'rel_id', 'db'=>'xt', 'table'=>'zzvw_cycle');
		$this->options['real_table'] = 'rel';
		
		$this->linkTables = array('m2m'=>array('os'));
		
		$this->options['edit'] = array('os_ids', 'rel_category_id', 'chip_type_id', 'chip_id', 'board_type_id', 'name', 'description', 'owner_id');
    } 
	
	// protected function getSpecialFilters(){
		// $special = array('prj_id');
		// return $special;
	// }
	
	// protected function specialSql($special, &$ret){
		// $this->rel_exist = count($special);
		// if ($this->rel_exist){
			// $condition = array('field'=>'testcase_last_result.prj_id', 'op'=>'in', 'value'=>$special[0]['value']);
			// $ret['main']['fields'] = " distinct rel.id, ".$ret['main']['fields'];
			// $ret['main']['from'] .= " LEFT JOIN testcase_last_result ON rel.id=testcase_last_result.rel_id";
			// $ret['where'] .= ' AND '.$this->tool->generateLeafWhere($condition);
		// }
	// }
	
}
