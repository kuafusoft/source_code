<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_jiraInfo extends action_jqgrid{

	public function handlePost(){
		//$params = $this->parseParams();
		$params = $this->params;
		//添加view
		if (!empty($params['id'])){	
			$username = $params['submit_username']; 
			$password = $params['submit_password']; 
			$restcurl = 'http://sw-jira.freescale.net/rest/api/latest';//根地址http://sw-jira.freescale.net/login.jsp
			$authurl = 'http://sw-jira.freescale.net/rest/auth/latest/session';
			$urls = array(
				'project' => $restcurl.'/project',//要采集的页面地址
				'priority' => $restcurl.'/priority',
			);
			
			//test database
			$X_Seraph_LoginReason = null;
			$headers = array( 
				"Accept: application/json",  
				"Content-Type: application/json",
			);
			$curl_params = array(
				CURLOPT_URL => $authurl,
				CURLOPT_HEADER => true, //为什么有httpheader但是header确实false？
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_USERPWD => "$username:$password",
				CURLOPT_VERBOSE => true, //如果设为true, 会打印所有过程信息
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				//CURLOPT_COOKIEJAR => $cookie_file
			);
			$ch = curl_init();  
			curl_setopt_array($ch, $curl_params); 			
			$access = 0;
			$cookie = '';
			foreach($urls as $key=>$url){
				curl_setopt($ch, CURLOPT_URL, $url);
				$result = curl_exec($ch); 
				$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
				$header = substr($result, 0, $headerSize);
// print_r($header);
				// if(empty($cookie)){
					// if(preg_match('/Set-Cookie:(.*);/iU',$header,$matches)){
						// $cookie = trim($matches[1]);
						// curl_setopt($ch,CURLOPT_COOKIE,$cookie);
					// }
				// }
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				if('200' != $http_code){
// print_r($header);
					$access = 2;
					$login_reason = '';
					if(preg_match('/X-Seraph-LoginReason:(.*)/i',$header,$matches)){
						$login_reason = trim($matches[1]);
					}
					if('AUTHENTICATED_FAILED' == $login_reason){
						$access = 3;
					}
					else if('AUTHENTICATION_DENIED' == $login_reason){
						$captcha_deny = '';
						if(preg_match('/X-Authentication-Denied-Reason:(.*);/i',$header,$matches)){
							$captcha_deny = trim($matches[1]);
						}
						if('CAPTCHA_CHALLENGE' == $captcha_deny){
							$access = 4;
						}
						else{
							$access = 3;
						}
					}
					break;
				}
				$result = substr($result, $headerSize);
				$ch_error = curl_error($ch); //curl 没有错误
				if ($ch_error){
					$access = 5;// curl wrong
					break;
				}
				//$jira_data[$key] = $result;
				$data = json_decode($result);
// print_r($data);
				$info[$key][0] = '';
				foreach($data as $detail){
					$info[$key][$detail->id] = $detail->name;
				}
			}
// print_r($info);
			curl_close($ch);
			if($access)
				return $access;	 
			$cols = array(
				array('name'=>'jira_project', 'label'=>'Project', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editrules'=>array('required'=>true), 'editoptions'=>array('value'=>$info['project'])),
				//array('name'=>'jira_issuetype', 'label'=>'JIRA Issue Type', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'text', 'editrules'=>array('required'=>true)),
				array('name'=>'jira_summary', 'label'=>'Summary', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'text', 'editrules'=>array('required'=>true)),
				array('name'=>'jira_priority', 'label'=>'Priority', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select', 'defval'=>3, 'editoptions'=>array('value'=>$info['priority'], 'defval'=>3)),
				array('name'=>'jira_customfield_10300', 'label'=>'Boards(s)', 'editable'=>true, 'type'=>'single_multi', 'init_type'=>'single', 
					'single_multi'=>array('db'=>'xt', 'table'=>'jira_boards', 'label'=>'JIRA Boards')),
				array('name'=>'jira_customfield_10301', 'label'=>'Tool Chain(s)', 'editable'=>true, 'type'=>'single_multi', 'init_type'=>'single', 
					'single_multi'=>array('db'=>'xt', 'table'=>'jira_tool_chains', 'label'=>'JIRA Tool Chain')),
				array('name'=>'jira_components', 'label'=>'Component(s)', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
				array('name'=>'jira_versions', 'label'=>'Affects Version(s)', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
				array('name'=>'jira_customfield_10403', 'label'=>'Reproducibility', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
				array('name'=>'jira_security', 'label'=>'Security', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
				array('name'=>'jira_customfield_10404', 'label'=>'Role', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
				array('name'=>'jira_description', 'label'=>'Description', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'textarea'),
				array('name'=>'jira_labels', 'label'=>'Labels', 'editable'=>true, 'DATA_TYPE'=>'int','type'=>'select'),
			);
			$this->renderView('jira_info.phtml', array('cols'=>$cols), '/jqgrid/xt/zzvw_cycle_detail');
		}	
	}
	
}

?>