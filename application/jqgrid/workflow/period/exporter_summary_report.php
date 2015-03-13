<?php
require_once('exporter_excel.php');

class workflow_period_exporter_summary_report extends exporter_excel{
	public function setOptions($jqgrid_action){
		parent::setOptions($jqgrid_action);
		// set the sheet1 for daily notes
		$daily_note = tableDescFactory::get('workflow', 'daily_note');
		$titles = $this->getTitle($daily_note);
// 隐藏daily_note_type, edit_status_id
		foreach($titles as $k=>&$v){
			if ($v['index'] == 'daily_note_type_id' || $v['index'] == 'edit_status_id'){
				$v['hidden'] = true;
				// break;
			}
		}
		//增加p_prj
		$p_prj = array('label'=>'Parent Project', 'index'=>'p_prj');
		array_unshift($titles, $p_prj);
		
		$data = $this->getDailyNotes($daily_note, $pre_text);
		$this->params['sheets'][1] = array('pre_text'=>$pre_text, 'title'=>'Items', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles)), 
			'data'=>$data, 'groups'=>array(array('index'=>'p_prj'), array('index'=>'prj_id')));
		// $sheet['groups'] = array(array('index'=>'prj', 'subtotal'=>array()), array('index'=>'compiler', 'subtotal'=>array('locate'=>'module', 'fields'=>$subtotalFields)));
//		$this->setGroupInfo();
	}

	protected function getDailyNotes($daily_note, &$pre_text){
		$data = array();
		$userAdmin = new Application_Model_Useradmin(null);
		$db = dbFactory::get('workflow');
		$res = $db->query("SELECT * FROM period WHERE id={$this->params['id']}");
		$p = $res->fetch();
		$pre_text = $p['name']."[".$p['from']." TO ".$p['end']."]";
		// 先过滤Level
		$creater_ids = implode(',', $userAdmin->getSubUsers($this->params['levels'], true));
		$period_where = 1;
		$res = $db->query("SELECT * FROM period WHERE id IN ({$this->params['id']})");
		if ($row = $res->fetch()){
			$period_where = "daily_note.created>='{$row['from']}' AND daily_note.created<='{$row['end']} 23:59:59'";
		}
		
		$sql = "SELECT daily_note.*, prj_trace.prj_id, prj_trace.progress, prj.name as prj, prj.ps, prj.pid".
			" FROM daily_note left join prj_trace on daily_note.id=prj_trace.daily_note_id".
			" LEFT JOIN prj ON prj_trace.prj_id=prj.id".
			" WHERE $period_where AND daily_note.creater_id in ($creater_ids)".
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
	
	// 设置Style
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$style = parent::calcStyle($sheetIndex, $headerIndex, $content, $default);
		
		if ($sheetIndex == 1 && $headerIndex == 'item_prop_id'){
			switch($content['item_prop_id']){
				case 'Normal': //normal
					break;
				case 'Highlight':
					$style = 'highlight';
					break;
				case 'Issue':
					$style = 'warning';
					break;
			}
		}
		return $style;
	}
};

?>
 