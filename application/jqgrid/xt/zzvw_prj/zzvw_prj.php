<?php

require_once('table_desc.php');

class xt_zzvw_prj extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'os_id'=>array('hidden'=>true),
			'chip_type_id'=>array('hidden'=>true),
			'chip_id'=>array('hidden'=>true),
			'board_type_id'=>array('hidden'=>true),
			'name'=>array('editable'=>false, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'description',
			'owner_id',
			'prj_status_id',
			'isactive',
			'*'=>array('hidden'=>true, 'editable'=>false)
		);
		$this->options['gridOptions']['label'] = 'Project';
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'prj_id', 'db'=>'xt', 'table'=>'zzvw_cycle');
		$this->options['real_table'] = 'prj';
//		$this->options['caption'] = 'Project';
		
		$this->options['edit'] = array('os_id', 'chip_type_id', 'chip_id', 'board_type_id', 'name', 'description', 'owner_id');
    } 
	
	protected function getButtons(){
        $buttons = array(
			'complete'=>array('caption'=>'Complete the PRJ'),
			'uncomplete'=>array('caption'=>'unComplete the PRJ'),
			'diff'=>array('caption'=>'Tell the difference'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
		return $buttons;
	}
	
	protected function getSpecialFilters(){
		$special = array('rel_id');
		return $special;
	}
	
	protected function specialSql($special, &$ret){
		$this->rel_exist = count($special);
		if ($this->rel_exist){
			$condition = array('field'=>'testcase_last_result.rel_id', 'op'=>'in', 'value'=>$special[0]['value']);
			$ret['main']['fields'] = " distinct rel.id, ".$ret['main']['fields'];
			$ret['main']['from'] .= " LEFT JOIN testcase_last_result ON prj.id=testcase_last_result.prj_id";
			$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($condition);
		}
	}
	
}
