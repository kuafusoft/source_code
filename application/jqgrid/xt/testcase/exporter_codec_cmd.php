<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_testcase_exporter_codec_cmd extends exporter_txt{
	protected function _export(){
		$db = dbFactory::get($this->params['db']);
		$ids = implode(',', $this->params['id']);
		$this->str .= "<playlist>";
		$sql = "SELECT tc.code, ver.command, module.name as module, ver.resource_link ".
			" FROM testcase tc left join testcase_ver ver on tc.id=ver.testcase_id ".
			" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			" left join testcase_module module on tc.testcase_module_id=module.id".
			" WHERE tc.id in ($ids) and link.prj_id={$this->params['prj_id']}".
			" and link.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
		$res = $db->query($sql);
		while($row = $res->fetch()){
			$this->str .= "\n\t<testcase>".
				"\n\t\t<title>{$row['code']}</title>".
				"\n\t\t<module>{$row['module']}</module>".
				"\n\t\t<cmdline>{$row['command']}</cmdline>".
				"\n\t\t<location>{$row['resource_link']}</location>".
				"\n\t</testcase>";
		}
		$this->str .= "\n</playlist>";
	}
};

?>
