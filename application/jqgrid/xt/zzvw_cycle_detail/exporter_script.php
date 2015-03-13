<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_zzvw_cycle_detail_exporter_script extends exporter_txt{
	protected function _export(){
// print_r($this->params)	;
		$ids = implode(',', $this->params['id']);
		$db = dbFactory::get($this->params['db']);
		$sql = "SELECT tc.code, ver.command ".
			" FROM cycle_detail left join testcase_ver ver on cycle_detail.testcase_ver_id=ver.id ".
			" left join testcase tc on tc.id=ver.testcase_id ".
			" WHERE cycle_detail.id in ($ids)";
		$res = $db->query($sql);
		while($row = $res->fetch()){
			$this->str .= $row['code'].' '.$row['command']."\n";
		}
	}
};

?>
