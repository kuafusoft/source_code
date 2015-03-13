<?php
require_once('table_desc.php');

class mcu_device extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name'=>array('editable'=>false, 'unique'=>true, 'editrules'=>array('required'=>true)),
			'series_id'=>array('editable'=>false),
			'vendor_id'=>array('editable'=>false),
		);
		
		$this->options['gridOptions']['label'] = 'Device';
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'device_id', 'db'=>'mcu', 'table'=>'zzvw_device_ver');
    }

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