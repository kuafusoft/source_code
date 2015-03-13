<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_getJiraVersions extends action_jqgrid{

	public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		$username = $params['submit_username']; 
		$password = $params['submit_password'];  
		$restcurl = 'http://sw-jira.freescale.net/rest/api/2';//根地址http://sw-jira.freescale.net/login.jsp
		$urls = array(
			'versions' => $restcurl.'/project/'.$params['value'].'/versions',//要采集的页面地址
		);
		
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
			CURLOPT_VERBOSE => false, //如果设为true, 会打印所有过程信息
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => false,
		) ; 
		$ch = curl_init();  
		curl_setopt_array($ch, $curl_params);  
		$access = true;
		foreach($urls as $key=>$url){
			curl_setopt($ch, CURLOPT_URL, $url);
			$result = curl_exec($ch); 
			$ch_error = curl_error($ch); 
			if ($ch_error){
				$access = false;
				break;
			}
			//$jira_data[$key] = $result;
			$data = json_decode($result);
			if(empty($data)){
				$access = false;
				break;
			}
			// $info[$key][0]['id']= 0;
			// $info[$key][0]['name'] = '';
			$i = 1;
			foreach($data as $detail){
				$info[$key][$i]['id'] = $detail->id;
				$info[$key][$i]['name'] = $detail->name;
				$i++;
			}
		}
		curl_close($ch);
		if(!$access)
			return 2;
		return json_encode($info['versions']);
	}
	
}

?>