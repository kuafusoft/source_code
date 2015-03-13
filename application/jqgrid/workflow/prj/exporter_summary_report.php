<?php
require_once(APPLICATION_PATH.'/jqgrid/workflow/period/exporter_summary_report.php');

class workflow_prj_exporter_summary_report extends workflow_period_exporter_summary_report{
	protected function getDailyNotes($daily_note, &$pre_text){
		$data = array();
		$userAdmin = new Application_Model_Useradmin(null);
		$db = dbFactory::get('workflow');
		$res = $db->query("SELECT * FROM period WHERE id={$this->params['period_id']}");
		$p = $res->fetch();
		$pre_text = $p['name']."[".$p['from']." TO ".$p['end']."]";
		// 先过滤Level
		$creater_ids = implode(',', $userAdmin->getSubUsers($this->params['levels'], true));
		$period_where = 1;
		$res = $db->query("SELECT * FROM period WHERE id IN ({$this->params['period_id']})");
		if ($row = $res->fetch()){
			$period_where = "daily_note.created>='{$row['from']}' AND daily_note.created<='{$row['end']} 23:59:59'";
		}
		$res = $db->query("SELECT * FROM prj WHERE id={$this->params['id']}");
		$prj = $res->fetch();
		$ps = $prj['ps'];
		$sql = "SELECT daily_note.*, prj_trace.prj_id, prj_trace.progress, prj.name as prj, prj.ps, prj.pid".
			" FROM daily_note left join prj_trace on daily_note.id=prj_trace.daily_note_id".
			" LEFT JOIN prj ON prj_trace.prj_id=prj.id".
			" WHERE $period_where AND daily_note.creater_id in ($creater_ids)".
			" AND prj.ps like '$ps%'".
			" ORDER BY pid ASC, prj_id ASC";
		$res = $db->query($sql);
		while($row = $res->fetch()){
			$row['p_prj'] = '';
			if (!empty($row['pid'])){
				// 查找其父节点
				$prj = $db->query("SELECT name from prj WHERE id={$row['pid']}");
				$prj_row = $prj->fetch();
				$row['p_prj'] = $prj_row['name'];
			}
			$data[] = $row;
		}
		return $data;
	}

	// protected function getDailyNotes($daily_note){
		// $data = $this->getData($daily_note, array(
			// array('field'=>'prj_id', 'op'=>'IN', 'value'=>$this->params['id']), 
			// array('field'=>'period_id', 'op'=>'IN', 'value'=>$this->params['period_id']), 
			// array('field'=>'levels', 'op'=>'=', 'value'=>$this->params['levels']))
		// );
		// return $data;
	// }
};

?>
 