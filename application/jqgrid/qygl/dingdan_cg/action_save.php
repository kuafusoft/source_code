<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_dingdan_cg_action_save extends action_save{
	protected function fillDefaultValues($action, &$pair, $db, $table){
		parent::fillDefaultValues($action, $pair, $db, $table);
		if($action == 'new'){
			$pair['dingdan_status_id'] = DINGDAN_STATUS_ZHIXING;
		}
	}
}

?>