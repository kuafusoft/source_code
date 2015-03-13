<?php
require_once(APPLICATION_PATH.'/jqgrid/action_import.php');

class xt_testcase_action_import extends action_import{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase';
		$res = $this->db->query("SELECT * FROM testcase_type");
		$types = array();
		while($row = $res->fetch()){
			$types[$row['id']] = $row['name'];
		}
		$view_params['testcase_type'] = $types;
		
		$config_types = array(
			'android_kk.karen.config.php'=>'Android KK By Karen',
			'psdk.bill.config.php'=>'PSDK Excel By Bill',
			'fas.amy.config.php'=>'FAS Excel By Amy',
			'fas.amy_1.config.php'=>'FAS TrickMode By Amy',
			'android.codec.config.php'=>'Android TrickMode By Codec Team',
			'shan_cmd_xml'=>'Update CMD by shan (Xml)',
			'codec_shan_update.config.php'=>'Update CMD by shan (Excel)',
			'codec_shan_update2.config.php'=>'Update Cases Info by shan (Excel)'
		);
		$view_params['config_file'] = $config_types;
		
		//  get the owner list
		$owner = $this->userAdmin->getUserList(array('role_id'=>ROLE_TESTER));
		$view_params['owner'] = $owner;
		return $view_params;
	}
	
	protected function handlePost(){
// print_r($this->params);
		if (!empty($this->params['config_file']))
			$this->params['config_file'] = APPLICATION_PATH.'/jqgrid/xt/testcase/'.$this->params['config_file'];
		
		$class = 'testcase';
		$config_file = basename($this->params['config_file']);
		switch($this->params['testcase_type_id']){
			case 2://codec
				if($config_file == 'shan_cmd_xml')
					$class = 'codec_cmd';
				else if($config_file == 'codec_shan_update.config.php' || $config_file == 'codec_shan_update2.config.php')
					$class = 'update';
				break;
			case 14:
				if($config_file == 'fas.amy.config.php' || $config_file == 'fas.amy_1.config.php'){
					$class = 'fas_testcase';
				}
				break;
		}
		if(!empty($class)){
			$importer = importerFactory::get($class, $this->params);
			$importer->setOptions($this);
			return $importer->import();
		}
	}
	
	
}

?>