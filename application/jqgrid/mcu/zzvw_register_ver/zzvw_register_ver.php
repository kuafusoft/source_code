<?php
require_once('table_desc.php');

class mcu_zzvw_register_ver extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'*'=>array('editable'=>false),
		);
		
		$this->options['gridOptions']['label'] = 'Register';
	}
	
	protected function _setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'register_ver_id', 'db'=>'mcu', 'table'=>'zzvw_field_ver');
    }

	// protected function setSubGrid(){
		// parent::setSubGrid();
		// unset($this->options['subGrid']['additional']['peripheral_ver_id']);
// // print_r($this->options['subGrid']);
	// }

	// protected function getButtons(){
		// $buttons = array(
			// 'gencode'=>array('caption'=>'Generate Code'),
			// 'diff'=>array('caption'=>'Diff')
		// );
		// return $buttons;
	// }
	
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