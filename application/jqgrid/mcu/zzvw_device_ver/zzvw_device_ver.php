<?php
require_once('table_desc.php');

class mcu_zzvw_device_ver extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id',
			'device_id'=>array('editable'=>false, 'type'=>'text'),
			'version'=>array('editable'=>false),
			'schema_version'=>array('editable'=>false),
			'description'=>array('editable'=>false),
			'address_unit_bits'=>array('editable'=>false, 'label'=>'Address Unit Bits'),
			'width'=>array('editable'=>false),
			'license_text'=>array('editable'=>false, 'hidden'=>true),
			
			'cpu_id'=>array('editable'=>false),
			'revision'=>array('editable'=>false),
			'endian_id'=>array('editable'=>false),
			'mpu_present'=>array('editable'=>false),
			'fpu_present'=>array('editable'=>false),
			'vtor_present'=>array('editable'=>false),
			'nvic_prio_bits'=>array('editable'=>false),
			'vendor_systick_config'=>array('editable'=>false),
			'created',
		);
		
		$this->options['gridOptions']['label'] = 'Device';
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'device_ver_id', 'db'=>'mcu', 'table'=>'zzvw_peripheral_ver');
    }

	protected function getButtons(){
		$buttons = array(
			'import'=>array('caption'=>'Upload'),
			'diff'=>array('caption'=>'Diff')
		);
		return $buttons;
	}
}
?>