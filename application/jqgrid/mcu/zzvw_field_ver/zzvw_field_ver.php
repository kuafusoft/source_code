<?php
require_once('table_desc.php');

class mcu_zzvw_field_ver extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'*'=>array('editable'=>false),
		);
		$this->options['gridOptions']['label'] = 'Device';
    }

	protected function _setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'field_ver_id', 'db'=>'mcu', 'table'=>'zzvw_enumerated_value');
	}
	
	// protected function setSubGrid(){
		// parent::setSubGrid();
		// unset($this->options['subGrid']['additional']['register_ver_id']);
// // print_r($this->options['subGrid']);
	// }

	protected function getButtons(){
		$buttons = array(
			'upload_doc'=>array('caption'=>'Upload'),
			'diff'=>array('caption'=>'Diff')
		);
		return $buttons;
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