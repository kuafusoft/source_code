<?php
require_once(APPLICATION_PATH.'/jqgrid/action_node_detail_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class workflow_daily_note_action_save extends action_node_detail_save{
	protected function save($db, $table, $params){
		$node_id = parent::save($db, $table, $params);
		//如果是prj_trace类型，并且涉及的prj_id是最底层节点，则应更新prj的progress
		if ($params['daily_note_type_id'] == DAILY_NOTE_TYPE_PRJ_TRACE){
			$res = $this->db->query("SELECT id from prj where pid=".$params['prj_id']);
			if (!$row = $res->fetch()){
				$this->db->update('prj_node', array('progress'=>$params['progress']), "id=".$params['prj_id']);
			}
		}
		return $node_id;
	}
}
?>