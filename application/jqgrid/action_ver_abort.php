<?php
require_once(APPLICATION_PATH.'/jqgrid/action_ver_action.php');

class action_ver_abort extends action_ver_action{
	protected function setHelpTable($helpTable = array()){
		parent::setHelpTable($helpTable);
		
		if (empty($helpTable['linkField']))
			$this->link_field = $this->ver_table.'_id';
	}

	protected function handlePost(){
		$table = $this->get('table');
        $params = $this->params;
		$ver = json_decode($params['ver'], true);
		$ver = implode(',', $ver);
		// 删除所有和该ver相关的记录，包括testcase_ver, prj_testcase_ver, prj_testcase_ver_history
		$sql = " DELETE ver, link, his FROM {$this->ver_table} ver LEFT JOIN {$this->link_table} link on ver.id=link.{$this->link_field} ".
			" LEFT JOIN {$this->history_table} his on ver.id=his.{$this->link_field}".
			" WHERE ver.id in ($ver) AND ver.edit_status_id NOT in (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")";
		$this->db->query($sql);
		// 得到下一个version，用来更新用户界面
		$res = $this->tool->query("SELECT * FROM {$this->ver_table} WHERE {$this->get('table')}_id={$params['id']} ORDER BY id DESC");
		if ($row = $res->fetch())
			return $row['id'];
		
		// 没有剩余的Version，则也应删除Case
		$this->tool->delete($this->get('table'), "id=".$params['id'], $this->get('db'));
		return 0;
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "view_edit_abort.phtml";
		$view_params['view_file_dir'] = '/jqgrid';

		return $view_params;
	}

}

?>