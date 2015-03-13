<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_checkMyname extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		if(!empty($params['myname'])){
			$name = '';
			$res = $this->tool->query("SELECT name FROM cycle WHERE id in (".implode(',', $params['id']).")");
			while($row = $res->fetch()){
				$myname = $row['name']."_".$params['myname'];
				$name_res = $this->tool->query("SELECT id FROM cycle WHERE name='".$myname."'");
				if($rows = $name_res->rowCount())
					$name[] = $myname."\n";	
			}
			if(!empty($name))
				return json_encode($name);
		}
	}
}
?>