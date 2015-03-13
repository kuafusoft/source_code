<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');
class mcu_device_ver_action_list extends action_list{
	protected function getUnknownInfoForRow($row, $field){
// print_r($field);
		$res = $this->tool->query("SELECT * FROM cpu_ver left join cpu_ver_device_ver on cpu_ver.id=cpu_ver_device_ver.cpu_ver_id WHERE cpu_ver_device_ver.device_ver_id={$row['id']}");
		$cc = $res->fetch();
		unset($cc['id']);
		$row = array_merge($row, $cc);
		return $row;
	}
	
	
}
?>