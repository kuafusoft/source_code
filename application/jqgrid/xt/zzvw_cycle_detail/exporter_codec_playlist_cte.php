<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_zzvw_cycle_detail_exporter_codec_playlist_cte extends exporter_txt{

	protected function _export(){
		$db = dbFactory::get($this->params['db']);
		// $this->params['id'] = json_decode($this->params['id']);
		$sql = "SELECT testcase.code, ver.command, ver.resource_link, testcase_module.name as module, testcase.summary".
			" FROM cycle_detail detail".
			" LEFT JOIN testcase_ver ver ON ver.id = detail.testcase_ver_id".
			" LEFT JOIN testcase ON testcase.id = ver.testcase_id".
			" LEFT JOIN testcase_module ON testcase.testcase_module_id = testcase_module.id".
			" WHERE detail.id in (".implode(",", $this->params['id']).")";//" WHERE detail.id in (".implode(",", $this->params['id']).")";
		$res = $db->query($sql);
		$str = "<?xml version='1.0'?>"."\n";
		$str .= "<!--This playlist is auto generated by CTE interface tool with xt, don't edit it unless you know what you are doing.-->"."\n";
		$str .= "\n";
		$str .= "<playlist>"."\n";
		while ($row = $res->fetch()){
			if(!empty($row['code'])){
				$str .= '	<testcase>'."\n";
				$str .= '		<testcaseid>'.$row['code'].'</testcaseid>'."\n";
				$str .= '		<cmdline>'.$row['command'].'</cmdline>'."\n";
				if($row['resource_link'] && $row['resource_link'] != ''){
					$resource_link = str_replace("\\", "/", $row['resource_link']);
					if(preg_match("/.*(\/)$/", $resource_link, $matches))
						$row['resource_link'] = $row['resource_link'];
					else
						$row['resource_link'] .= "\\";
				}
				$str .= '		<location>'.$row['resource_link'].'</location>'."\n";
				$str .= '		<module>'.$row['module'].'</module>'."\n";
				$str .= '		<title>'.trim($row['summary']).'</title>'."\n";
				$str .= '	</testcase>'."\n";
			}
		}
		$str .= '</playlist>';
		$index = strrpos($this->fileName, ".txt");
		$name = substr($this->fileName, 0, $index);
		$this->fileName = $name.".xml";
		$this->str  = $str;
	}
};
?>