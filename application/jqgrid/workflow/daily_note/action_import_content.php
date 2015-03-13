<?php
require_once('action_jqgrid.php');

class workflow_daily_note_action_import_content extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		//从哪里导入数据？可以有多个选择：daily note, 
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
		$view_params['view_file'] = 'import_content.phtml';
		$view_params['view_file_dir'] = '/jqgrid/workflow/daily_note';
//print_r($view_params);		
		return $view_params;
	}
}

?>