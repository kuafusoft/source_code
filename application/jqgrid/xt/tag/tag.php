<?php
require_once('table_desc.php');

class xt_tag extends table_desc{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id'=>array('hidden'=>true), 
			'name'=>array('editable'=>false),
			'db_table'=>array('editable'=>false),
			'id_field'=>array('editable'=>false),
			'element_ids'=>array('formatter'=>'text'),
			'public',
			'creater_id'=>array('editable'=>false),
			'modified'=>array('editable'=>false),
		);
		if (!$this->userAdmin->isAdmin($this->userInfo->id)){
			$rules = array(array('field'=>'creater_id', 'op'=>'eq', 'data'=>$this->userInfo->id));
			$this->params['searchConditions'] = $this->tool->generateFilterConditions($rules);
			foreach($this->params['searchConditions'] as $k=>$cond){
				$this->params['condMap'][$cond['field']] = $cond;
			}
		}
// print_r($this->params);		
		$this->options['navOptions']['del'] = true;
	}
	
	protected function getButtons(){
		return array();
	}
	
	protected function config($trimed = true, $params){
		parent::config($trimed, $params);
		unset($this->options['tags']);
	}
}
?>