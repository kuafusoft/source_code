<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_gx_action_save extends action_save{
	protected function afterSave($affectID){
		$this->tool->delete('gx_input', "gx_id=$affectID");
		$this->tool->delete('gx_output', "gx_id=$affectID");
		if(!empty($this->params['gx_input']['data'])){
			$gx_input = $this->params['gx_input']['data'];
			foreach($gx_input as $item){
				$item['gx_id'] = $affectID;
				$this->tool->insert('gx_input', $item);
			}
		}
		
		if(!empty($this->params['gx_output']['data'])){
			$gx_output = $this->params['gx_output']['data'];
			foreach($gx_output as $item){
				$item['gx_id'] = $affectID;
				$this->tool->insert('gx_output', $item);
			}
		}
	}
}

?>