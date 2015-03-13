<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_gx_de_action_save extends action_save{
	protected function afterSave($affectID){
		$gx_de_input = $this->params['gx_de_input']['data'];
		foreach($gx_de_input as $item){
			$item['gx_de_id'] = $affectID;
			$this->tool->insert('gx_de_input', $item);
		}
		
		$gx_de_output = $this->params['gx_de_output']['data'];
		foreach($gx_de_output as $item){
			$item['gx_de_id'] = $affectID;
			$this->tool->insert('gx_de_output', $item);
		}
	}
}

?>