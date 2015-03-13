<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getJiraComponents extends action_jqgrid{

	public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$username = $params['submit_username']; 
		$password = $params['submit_password'];  
		$restcurl = 'http://sw-jira.freescale.net/rest/api/2';//根地址http://sw-jira.freescale.net/login.jsp
		$url =  $restcurl.'/issue/createmeta?projectIds='.$params['jira_project'].'&issuetypeNames=Bug&expand=projects.issuetypes.fields';
		//test database
		$headers = array( 
			"Accept: application/json",  
			"Content-Type: application/json"  
		); 
		$curl_params = array(
			CURLOPT_HEADER => false, //为什么有httpheader但是header确实false？
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPGET => true,
			CURLOPT_USERPWD => "$username:$password",
			CURLOPT_VERBOSE => true, //如果设为true, 会打印所有过程信息
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		) ; 
		$ch = curl_init();  
		curl_setopt_array($ch, $curl_params);  
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch); 
		$ch_error = curl_error($ch); 
		curl_close($ch);
		if ($ch_error) { 
			print_r("~~~~~~~~~~~~~~~~~~~~~~"); 
			echo "cURL Error: $ch_error"; 
		} else { 
		// print_r($result);
			$result = json_decode($result, true); 
			$required_fields = array('reporter'=>'customfield_10404', 'reproducibility'=>'customfield_10403', 'boards'=>'customfield_10300', 
				'toolchain'=>'customfield_10301', 'components', 'versions', 'labels', 'environments', 'security');
			foreach($required_fields as $field){
				$data["jira_".$field]['value'] = array();
			}
			foreach($result['projects'] as $project){
				foreach($project['issuetypes'] as $issuetype){
					foreach($issuetype['fields'] as $key=>$field){
// print_r($key."\n");
// print_r($field);
						if(empty($field['allowedValues']))
							continue;

						if(!in_array($key, $required_fields))
							continue;
						$i = 0;

						foreach($field['allowedValues'] as $value){
							if(!empty($value)){
								if(!empty($value['value'])){
									$data["jira_".$key]['value'][$i]['name'] = $value['value'];
									$data["jira_".$key]['value'][$i]['id']= $value['id'];
								}
								else if(!empty($value['name'])){
									$data["jira_".$key]['value'][$i]['name'] = $value['name'];
									$data["jira_".$key]['value'][$i]['id']= $value['id'];
								}
								if(!empty($data["jira_".$key]['value'][$i])){
									if('customfield_10300' == $key){
										$insert = array('id'=>$data["jira_".$key]['value'][$i]['id'], 'name'=>$data["jira_".$key]['value'][$i]['name'], 'jira_project'=>$params['jira_project']);
										$this->tool->getElementId("jira_boards", $insert);
									}
									else if('customfield_10301' == $key){
										$insert = array('id'=>$data["jira_".$key]['value'][$i]['id'], 'name'=>$data["jira_".$key]['value'][$i]['name'], 'jira_project'=>$params['jira_project']);
										$this->tool->getElementId("jira_tool_chains", $insert);
									}
								}
								$i++;
							}
						}
						if(!empty($data["jira_".$key]['value'])){
							switch($key){
								case 'customfield_10403':
									$data["jira_".$key]['defval'] = '10311';
									break;
								case 'customfield_10404':
									$data["jira_".$key]['defval'] = '10321';
									break;
								case 'security':
									$data["jira_".$key]['defval'] = '10000';
									break;
							}
						}
					}
				}
			}
			return json_encode($data);
		}
	}
}

?>