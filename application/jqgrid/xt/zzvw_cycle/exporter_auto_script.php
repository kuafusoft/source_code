<?php
require_once('dbfactory.php');
require_once('exporter_txt.php');

class xt_zzvw_cycle_exporter_auto_script extends exporter_txt{
	protected function _export(){
		$db = dbFactory::get($this->params['db']);
		$sql = "SELECT * FROM zzvw_cycle_detail".
			" WHERE cycle_id =".$this->params['id']." AND auto_level_id IN (".$this->getAutoLevel().")";
		$result = $db->query($sql);
		$str = '';
		while ($row = $result->fetch()){
			if(!empty($row["command"]))
				$this->str .= $row["testcase_id"] . " " . $row["command"] . "\n";
		}
	}
	
	protected function getAutoLevel(){
		return AUTO_LEVEL_AUTO;
	}
};
?>