<?php
require_once('action_jqgrid.php');

class action_beforeSave extends action_jqgrid{
	protected function _execute(){
		$options = $this->table_desc->getOptions();
		$linkInfos = isset($options['linkTables']['node_ver_m2m']) ? $options['linkTables']['node_ver_m2m'] : array();
		if(!empty($linkInfos)){
			foreach($linkInfos as $linkInfo){
			
			}
		}
		return '';
	}
}
?>