<?php

require_once('table_desc.php');

class xt_testcase_type extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name'=>array('editable'=>true, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'os_id'=>array('editable'=>true, 'search'=>true),
			'groups_id'=>array('editable'=>true, 'search'=>true),
		);
		if(!empty($this->params['container']) && $this->params['container'] == 'select_cart'){
			unset($this->options['list']);
			$this->options['list'] = array(
				'id', 
				'name'=>array('label'=>'TestCase Type Name'),
				'isactive'=>array('defval'=>1, 'hidden'=>true)
			);
		}
		$this->options['gridOptions']['label'] = 'Testcase Type';
		$this->linkTables = array('m2m'=>array(
			'os', 
			array('link_db'=>'xt', 'link_table'=>'group_testcase_type', 'link_field'=>'group_id', 'self_link_field'=>'testcase_type_id', 'db'=>'useradmin', 'table'=>'groups')
		));
    } 
	
	public function accessMatrix(){
		// $access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = $access_matrix['assistant_admin']['all'] = true;
		$access_matrix['assistant_admin']['view_edit_edit'] = false;
		return $access_matrix;
	}
	
	protected function _getLimit($params){
		$limit = array();
		// _P("SELECT testcase_type_id FROM group_testcase_type WHERE group_id in ({$this->userInfo->group_ids})");
		$res = $this->tool->query("SELECT testcase_type_id FROM group_testcase_type WHERE group_id in ({$this->userInfo->group_ids})");
		while($row = $res->fetch())
			$limit[] = $row['testcase_type_id'];
		return $limit;
	}
	
}
?>