<?php
require_once('table_desc.php');

class mcu_zzvw_peripheral_ver extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id',
			'*'=>array('editable'=>false)
		);
		$this->options['gridOptions']['label'] = 'Peripheral';
	}
	
	protected function _setSubGrid(){
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'peripheral_ver_id', 'db'=>'mcu', 'table'=>'zzvw_register_ver');
    }

	protected function getButtons(){
		$buttons = array(
			'gencode'=>array('caption'=>'Generate Code'),
		);
		return $buttons;
	}
}
?>