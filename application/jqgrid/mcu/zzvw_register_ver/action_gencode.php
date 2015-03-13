<?php
require_once('action_jqgrid.php');//APPLICATION_PATH.'/jqgrid/action_ver_diff.php');
class mcu_zzvw_register_ver_action_gencode extends action_jqgrid{ //生成.h文件
	protected function handlePost(){
		print_r($this->params);
		foreach($this->params['id'] as $register_ver_id){
			
		}
	}

}
?>