<?php
require_once(APPLICATION_PATH.'/jqgrid/action_import.php');

class xt_zzvw_cycle_action_import extends action_import{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
// print_r($view_params);
		$view_params['view_file_dir'] = '/jqgrid/xt/zzvw_cycle';
		// $res = $this->tool->query("SELECT * FROM testcase_type");
		// $types[0] = '';
		// while($row = $res->fetch()){
			// $types[$row['id']] = $row['name'];
		// }
		$view_params['testcase_type']['value'] = array('0'=>'', '9'=>'MQX', '2'=>'CODEC', '1'=>'LinuxBsp');//, '3'=>'Wince bsp');
		$config_types = array(
			'0'=>'',
			//'5'=>'update testcase_last_result',
			//'4'=>'Update Clone For Liu Fuchuan',
		);
		$res = $this->tool->query("select id, name from prj");
		$prj[0] = '';
		while($info = $res->fetch()){
			$prj[$info['id']] = $info['name'];
		}
		$view_params['prj']['value'] = $prj;
		$res = $this->tool->query("select id, name from test_env where isactive = 1");
		$env[0] = '';
		while($info = $res->fetch()){
			$env[$info['id']] = $info['name'];
		}
		$view_params['test_env']['value'] = $env;
		//  get the owner list
		// $owner = $this->userAdmin->getUserList(array('role_id'=>ROLE_TESTER));
		$owner = array(48=>'Admin FSL', 65=>'Jian Zhang');
		$view_params['owner']['value'] = $owner;
		
		if(!empty($params['id'])){
			// $res = $this->tool->query("select codec_stream_id from cycle_detail where cycle_id = ".$params['id']);
			// if($info = $res->fetch()){
				$view_params['id'] = $params['id'];
				$view_params['testcase_type']['value'] = array('0'=>'', '1'=>'LinuxBSP', '2'=>'CODEC');
				$owner = $this->userAdmin->getUserList(array('role_id'=>ROLE_TESTER, 'group_id'=>3));
				$view_params['owner']['value'] = $owner;
				$config_types = array(
					'0'=>'',
					'1'=>'Results By Apollo GVB (log)',
					'jellybean_codec_without_cte.config.php'=>'Results By CTE (excel)',
					'2'=>'Results By LinuxBSP (txt)',
					'3'=>'Results By Apollo Android Codec(xml)',
				);
			// }
			$res = $this->tool->query("select testcase_type_id, creater_id, test_env_id, prj_id from cycle where id = ".$params['id']);
			if($info = $res->fetch()){
				$view_params['defval']['testcase_type_id'] = $info['testcase_type_id'];
				$view_params['defval']['test_env_id'] = $info['test_env_id'];
				$view_params['defval']['prj_ids'] = $info['prj_id'];
			}
		}
		$view_params['defval']['owner_id']= $this->userInfo->id;
		$view_params['config_file'] = $config_types;
		return $view_params;
	}
	
	protected function handlePost(){
		if (!empty($this->params['config_file']))
			$this->params['config_file'] = APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle/'.$this->params['config_file'];
		$base_name = basename($this->params['config_file']);
		switch($this->params['testcase_type_id']){
			case 1://linux
				$class = 'LinuxBSP_auto';
				if($base_name == '4')
					$class = 'update_clone';
				break;
			case 2://codec
				switch($base_name){
					case "1":
						$class = 'codec_apollo_gvb';
						break;
					case "2":
						$class = 'caseVer';
						break;
					case "3":
						$class = 'codec_apollo_android';
						break;
					case 'jellybean_codec_without_cte.config.php':
						$class = 'codec_cte';
						break;
				}
				break;
			case 3://wince bsp
				//if($base_name = 5)
				//	$class = 'update_testcase_last_result';
				break;
			case 4://Android Application
				break;
			case 5://Android User Case
				break;
			case 6:// a robot case
				break;
			case 7://wireless charging
				break;
			case 9://mqx
				$class = 'mqx';				
				break;
			case 14:
				$class = 'fas_wrong';
			default:
				print_r("No This Type Now!");
				break;
		}
		if(!empty($class)){
			$importer = importerFactory::get($class, $this->params);
			$importer->setOptions($this);
			return $importer->import();
		}
		print_r("This Feaure Does Not Finish");
	}
	
	
}

?>