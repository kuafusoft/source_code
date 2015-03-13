<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_testcase_exporter_bsp_config extends exporter_txt{
	protected function _export(){
		$db = dbFactory::get($this->params['db']);
		$sql = "SELECT chip.name as chip, os.name as os, board_type.name as board_type".
			" FROM prj left join chip on prj.chip_id=chip.id".
			" left join os on prj.os_id=os.id".
			" left join board_type on prj.board_type_id=board_type.id".
			" WHERE prj.id={$this->params['prj_id']}";
		$res = $db->query($sql);
		$row = $res->fetch();
		$this->str .= "chip={$row['chip']}, os={$row['os']}, board_type={$row['board_type']}\n";
		
		$ids = implode(',', $this->params['id']);
		$sql = "SELECT tc.code, ver.config ".
			" FROM testcase_ver ver left join testcase tc on tc.id=ver.testcase_id ".
			" left join prj_testcase_ver link on link.testcase_ver_id=ver.id".
			" WHERE tc.id in ($ids) and link.prj_id={$this->params['prj_id']}".
			" and ver.edit_status_id in (".EDIT_STATUS_PUBLISHED.",".EDIT_STATUS_GOLDEN.")";
		$res = $db->query($sql);
		while($row = $res->fetch()){
			$this->str .= $row['code'].' '.$row['config']."\n";
		}
	}
};

?>
