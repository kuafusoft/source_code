<?php
require_once('action_jqgrid.php');
class xt_testcase_ver_action_rel_script extends action_jqgrid{
	protected function handlePost(){
        $params = $this->params;
		$fileName = "tmp.txt";
        if ($this->controller->getRequest()->isPost()){
//print_r($params);
			$str = '';
			switch($params['report_type']){
				case 1: // Release Report
					$fileName = "case_release.html";
					$str = $this->generateHTMLHeader("Case Release");
					$str .= "<body>";
					$str .= $this->generateIntro($params);
					$str .= $this->generateSetup($params);
					$str .= $this->generateTestCase($params);
					$str .= "</body></html>";
					break;
				case 2: // cmd file for linux BSP
					$fileName = "cmd4linuxbsp.txt";
					$sql = "SELECT tc.code, ver.command ".
						" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" WHERE tc.id in (".implode(',', json_decode($params['id'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->tool->query($sql);
					while($row = $res->fetch()){
						$str .= $row['code'].' '.$row['command']."\n";
					}
					break;
				case 3://cmd file for codec 
					$fileName = "cmd4codec.txt";
/*					
<playlist>
  <testcase>
      <title>AAC_LC_24kHz_128kbps_2_Main.aac</title>
      <module>AACLCDec</module>
      <cmdline></cmdline>
      <location>\AACLCDec\Conformance\ADIF\</location>
  </testcase>
*/					
					$str = "<playlist>";
					$sql = "SELECT tc.code, ver.command, module.name as module, ver.resource_link ".
						" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" left join testcase_module module on tc.testcase_module_id=module.id".
						" WHERE tc.id in (".implode(',', json_decode($params['id'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->tool->query($sql);
					while($row = $res->fetch()){
						$str .= "\n\t<testcase>".
							"\n\t\t<title>{$row['code']}</title>".
							"\n\t\t<module>{$row['module']}</module>".
							"\n\t\t<cmdline>{$row['command']}</cmdline>".
							"\n\t\t<location>{$row['resource_link']}</location>".
							"\n\t</testcase>";
					}
					$str .= "\n</playlist>";
					break;
				case 4: // xml cmd file for codec
					$fileName = "cmd4codec.xml";
					$str = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
					$str .= "<!--\n";
					$str .= "XML playlist used for Freescale codec test, based on XiaoTian test system.\n";
					$str .= "-->\n";
					$str .= "<playlist>\n";
					
					$sql = "SELECT tc.code, tc.summary, testcase_type.name as type, module.name as module, testpoint.name as testpoint, testcase_category.name as category, ".
						" testcase_priority.name as priority, testcase_source.name as source, auto_level.name as auto_level, ".
						" ver.command, ver.resource_link, ver.objective, ver.precondition, ver.steps, ver.expected_result, ".
						" stream.name, stream.location, codec_stream_container.name as container, codec_stream_v4cc.name as v4cc,".
						" stream.v_width, stream.v_height, stream.v_framerate, stream.v_bitrate, stream.chromasubsampling,".
						" stream.v_track, codec_stream_a_codec.name as a_codec, stream.a_samplerate, stream.a_channel, stream.a_track, stream.a_bitrate,".
						" stream.subtitle, stream.duration".
						" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
						" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
						" left join testcase_type on tc.testcase_type_id=testcase_type.id".
						" left join testcase_module module on tc.testcase_module_id=module.id".
						" left join testcase_source on tc.testcase_source_id=testcase_source.id".
						" left join testcase_priority on ver.testcase_priority_id=testcase_priority.id".
						" left join auto_level on ver.auto_level_id=auto_level.id".
						" left join testcase_category on tc.testcase_category_id=testcase_category.id".
						" left join testcase_testpoint testpoint on tc.testcase_testpoint_id=testpoint.id".
						" left join codec_stream stream on stream.testcase_id=tc.id".
						" left join codec_stream_container on stream.codec_stream_container_id=codec_stream_container.id".
						" left join codec_stream_v4cc on stream.codec_stream_v4cc_id=codec_stream_v4cc.id".
						" left join codec_stream_a_codec on stream.codec_stream_a_codec_id=codec_stream_a_codec.id".
						" WHERE tc.id in (".implode(',', json_decode($params['id'])).") and link.prj_id=".$params['prj_ids'].
						" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
					$res = $this->tool->query($sql);
					while($row = $res->fetch()){
						$str .= "  <teststream id=\"". $row['code'] . "\">\n";
						$str .= "      <caseinfo>\n";
						$str .= "          <type>" . $row['type'] . "</type>\n";
						$str .= "          <module>" . $row['module'] . "</module>\n";
						$str .= "          <testpoint>" . $row['testpoint'] . "</testpoint>\n";
						$str .= "          <category>" . $row['category'] . "</category>\n";
						$str .= "          <priority>" . $row['priority'] . "</priority>\n";
						$str .= "          <source>" . $row['source'] . "</source>\n";
						$str .= "          <autolevel>" . $row['auto_level'] . "</autolevel>\n";
						$str .= "          <objective><![CDATA[" . $row['objective'] . "]]></objective>\n";
						$str .= "          <environment><![CDATA[" . $row['precondition'] . "]]></environment>\n";
						$str .= "          <steps><![CDATA[" . $row['steps'] . "]]></steps>\n";
						$str .= "          <expected><![CDATA[" . $row['expected_result'] . "]]></expected>\n";
						$str .= "          <cmdline><![CDATA[" . $row['command'] . "]]></cmdline>\n";
						$str .= "          <location><![CDATA[" . $row['resource_link'] . "]]></location>\n";
						$str .= "      </caseinfo>\n";
						$str .= "      <streaminfo>\n";
						$str .= "          <clipname><![CDATA[" . $row['summary'] . "]]></clipname>\n";
						$str .= "          <container>" . $row['container'] . "</container>\n";
						$str .= "          <video>\n";
						$str .= "              <v4cc>" . $row['v4cc'] . "</v4cc>\n";
						$str .= "              <v_width>" . $row['v_width'] . "</v_width>\n";
						$str .= "              <v_height>" . $row['v_height'] . "</v_height>\n";
						$str .= "              <v_framerate>" . $row['v_framerate'] . "</v_framerate>\n";
						$str .= "              <v_bitrate>" . $row['v_bitrate'] . "</v_bitrate>\n";
						$str .= "              <chromasubsampling>" . $row['chromasubsampling'] . "</chromasubsampling>\n";
						$str .= "              <v_track>" . $row['v_track'] . "</v_track>\n";
						$str .= "          </video>\n";
						$str .= "          <audio>\n";
						$str .= "              <a_codec>" . $row['a_codec'] . "</a_codec>\n";
						$str .= "              <a_samplerate>" . $row['a_samplerate'] . "</a_samplerate>\n";
						$str .= "              <a_bitrate>" . $row['a_bitrate'] . "</a_bitrate>\n";
						$str .= "              <a_channel>" . $row['a_channel'] . "</a_channel>\n";
						$str .= "              <a_track>" . $row['a_track'] . "</a_track>\n";
						$str .= "          </audio>\n";
						$str .= "          <duration>" . $row['duration'] . "</duration>\n";
						$str .= "          <others>\n";
						$str .= "              <subtitle>" . $row['subtitle'] . "</subtitle>\n";
						$str .= "          </others>\n";
						$str .= "      </streaminfo>\n";
						$str .= "  </teststream>\n";
					}
					$str .= "</playlist>";					
					break;
			}
			$fileName = $this->tool->saveFile($str, $fileName);
			return $fileName;
        }
	}

	private function generateHTMLHeader($title){
		$str = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">";
		$str .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">";
		$str .= "<head>";
		$str .= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />";
		$str .= "<title>$title</title>";
		$str .= "<script type='text/javascript'>
			function displayDiv(id){
				var div = document.getElementById(id);
				if (div){
					var display = div.style.display;
					if (display == 'none')
						display = 'block';
					else
						display = 'none';
					div.style.display = display;
				}
			}
			</script>";
		$str .= "<style type='text/css'>
		<!--
		a:link {
			text-decoration: none;
		}
		a:visited {
			text-decoration: none;
		}
		a:hover {
			text-decoration: none;
		}
		a:active {
			text-decoration: none;
		}
		div{
			position:relative; left:30px;
		}
		.module{
			background-color: #DDDDDD;
			
		}
		.moduledesc{
			background-color: #EEEEEE;
		}
		.case{
			background-color: #DDDDDD;
		}
		-->
		</style>
		";
		
		$str .= "</head>";
		return $str;
	} 

	private function generateIntro($params){
		if(empty($params['intro']))
			$params['intro'] = "Intro";
		$str = "<a href='javascript:displayDiv(\"div_intro\")'><strong>Introduction</strong></a><BR />";
		$str .= "<div id='div_intro' style='display:none'>";
		$str .= $params['intro'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateSetup($params){
		if (empty($params['setup']))
			$params['setup'] = "Setup";
		$str = "<a href='javascript:displayDiv(\"div_setup\")'><strong>Setup Test Environment</strong></a><BR />";
		$str .= "<div id='div_setup' style='display:none'>";
		$str .= $params['setup'];
		$str .= "</div>";
		return $str;
	}
	
	private function generateTestCase($params){
		$module = "";
		$str = "";
		$sql = "SELECT tc.id, tc.code, tc.summary, module.id as module_id, module.name as module, category.name as category, source.name as source, ".
			" auto_level.name as auto_level, ver.manual_run_minutes, ver.auto_run_minutes, ver.objective, ver.precondition, ver.steps, ver.expected_result, ver.command ".
			" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
			" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" left join testcase_category category on tc.testcase_category_id=category.id".
			" left join testcase_source source on tc.testcase_source_id=source.id".
			" left join auto_level on ver.auto_level_id=auto_level.id".
			" WHERE tc.id in (".implode(',', json_decode($params['id'])).") and link.prj_id=".$params['prj_ids'].
			" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")".
			" GROUP BY ver.id".
			" ORDER BY module ASC";
		$res = $this->db->query($sql);
		$change = false;
		while($row = $res->fetch()){
			if ($row['module'] != $module){//module切换
				if (!empty($module))
					$str .= "</div>";
				$module = $row['module'];
				$str .= "<BR /><a href='javascript:displayDiv(\"div_module_{$row['module_id']}\")'><strong>{$row['module']}</strong></a><BR />";
				$str .= "<div id='div_module_{$row['module_id']}' style='display:none'>";
			}
			$str .= $this->generateTCHTML($row);
		}
		$str .= "</div>";
		return $str;
	}
	
	private function generateTCHTML($row){
		$fields = array('summary'=>'Name',
						'category'=>'Category',
						'auto_level'=>'Auto Level',
						'manual_run_minutes'=>'Manual Run Time',
						'auto_run_minutes'=>'Auto Run Time',
						'command'=>'Command',
						'objective'=>'Objective',
						'precondition'=>'Environment',
						'steps'=>'Steps',
						'expected_result'=>'Expected Result',
		);
		$str = "<p /><a href='javascript:displayDiv(\"div_case_{$row['id']}\")'>".$row['code'].":".$row['summary']."</a>";
		$str .= "<div id='div_case_{$row['id']}' style='display:none'><table border='1' bgcolor='#EEEEEE' width='100%'>";
		
		$currentRow = 0;
		foreach($fields as $key=>$caption){
			$content = $row[$key];
			if ($key == 'command' || $key == 'objective' || $key == 'steps' || $key == 'precondition' || $key == 'expected_result')
				$content = str_replace("\n", '<br />', $content);
			if (empty($content))
				$content = "&nbsp";
			$bgColor = '#CCCCCC';
			if ($currentRow % 2)
				$bgColor = '#DDDDDD';
			$str .= sprintf("<tr style='background-color:%s; color:blue'>
				<td width='15%%'>%s:</td>
				<td width='85%%'>%s</td>
				</tr>\n", $bgColor, $caption, $content);
			$currentRow ++;        
		}
		$str .= "</table></div>\n";
		return $str;
	}
		
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "rel_script.phtml";
		$view_params['view_file_dir'] = '/jqgrid/xt/testcase_ver';

		return $view_params;
	}

}
?>