<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle/export_auto_script.php');

class xt_zzvw_cycle_exporter_skywalker_script extends xt_zzvw_cycle_exporter_auto_script{
	protected function getAutoLevel(){
		return AUTO_LEVEL_AUTO.','.AUTO_LEVEL_MANUAL.','.AUTO_LEVEL_PARTIAL_AUTO;
	}
};
?>