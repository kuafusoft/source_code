<?php
require_once('action_jqgrid.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class workflow_work_summary_detail_action_import_note extends action_jqgrid{
	protected function handlePost(){
// print_r($this->params);
		$ids = implode(',', $this->params['id']);
		$existed = array();
		$sql = "SELECT daily_note_id FROM work_summary_detail WHERE work_summary_id={$this->params['parent']} AND daily_note_id IN ($ids)";
// print_r($sql);
		$res = $this->db->query($sql);
		while($row = $res->fetch())
			$existed[] = $row['daily_note_id'];
		$new = array_diff($this->params['id'], $existed);
		$news = implode(',', $new);
		if (!empty($news)){
			$sql = "SELECT daily_note.id as daily_note_id, daily_note.content as content, daily_note.item_prop_id, daily_note_type.table_name ".
				" FROM daily_note left join daily_note_type on daily_note.daily_note_type_id=daily_note_type.id WHERE daily_note.id IN ($news)";
			$res = $this->db->query($sql);
			while($row = $res->fetch()){
				$table_name = trim($row['table_name']);
				if (!empty($table_name)){
					$tmp = $this->db->query("SELECT * FROM $table_name WHERE daily_note_id={$row['id']}");
					$tmp_row = $tmp->fetch();
					if (!empty($tmp_row['prj_id']))
						$row['prj_id'] = $tmp_row['prj_id'];
				}
				$row['prj_id'] = isset($row['prj_id']) ? $row['prj_id'] : 0;
				$row['work_summary_id'] = $this->params['parent'];
				unset($row['table_name']);
				$this->db->insert('work_summary_detail', $row);
			}
		}
		return 0;
	}
}
?>