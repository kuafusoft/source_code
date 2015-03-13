<?php
require_once(APPLICATION_PATH.'/jqgrid/action_export.php');

class workflow_prj_action_export extends action_export{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		// get the right periods:所有包含当前时间的periods和当前时间之前5个periods
		$periods = array();
		$currentDate = date('Y-m-d');
		$sql = "SELECT * FROM period WHERE `from`<='$currentDate' AND `end`>='$currentDate'";
//print_r($sql);
		$res = $this->db->query($sql);
		while($row = $res->fetch())
			$periods[$row['id']] = $row['name']."[".$row['from']." TO ".$row['end']."]";
			
		$sql = "SELECT * FROM period WHERE `end`<'$currentDate' ORDER BY `from` DESC limit 0, 5";
		$res = $this->db->query($sql);
		while($row = $res->fetch())
			$periods[$row['id']] = $row['name']."[".$row['from']." TO ".$row['end']."]";
		
		$view_params['periods'] = $periods;
		$view_params['view_file_dir'] = '/jqgrid/workflow/prj';
		return $view_params;
	}
}

?>