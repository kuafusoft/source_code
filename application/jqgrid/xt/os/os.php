<?php
require_once('table_desc.php');

class xt_os extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name'=>array('editable'=>true, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'testcase_type_id'=>array('editable'=>true),
			'build_target_id'=>array('editable'=>true),
			'compiler_id'=>array('editable'=>true),
			'groups_id'=>array('editable'=>true, 'data_source_db'=>'useradmin'),
			'rel_id'=>array('editable'=>true, 'search'=>false),
			'isactive'
		);
		if(!empty($this->params['container']) && $this->params['container'] == 'select_cart'){
			unset($this->options['list']);
			$this->options['list'] = array(
				'id', 
				'name'=>array('label'=>'OS Name'),
				'isactive'=>array('defval'=>1, 'hidden'=>true)
			);
		}
		$this->options['gridOptions']['label'] = 'OS';
		$this->linkTables = array('m2m'=>array('testcase_type', 'build_target', 'compiler', array('table'=>'groups', 'db'=>'useradmin'), 'rel'));
    } 
	
	// protected function _getLimit($params){
		// return array(1, 2, 3, 4, 5, 10);
	// }
	public function accessMatrix(){
		// $access_matrix = parent::accessMatrix();
		$access_matrix['all']['all'] = false;
		$access_matrix['admin']['all'] = $access_matrix['assistant_admin']['all'] = true;
		return $access_matrix;
	}
}
?>