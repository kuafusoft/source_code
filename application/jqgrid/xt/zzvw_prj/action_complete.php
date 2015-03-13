<?php
require_once('jqgrid_action.php');

class xt_zzvw_prj_action_complete extends jqgrid_action{
	protected function handlePost(){
		$this->db->update('prj', array('prj_status_id'=>PRJ_STATUS_COMPLETED), "id in (".implode(',', json_decode($this->params['id'])).")");
	}
}

?>