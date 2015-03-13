<?php
require_once(APPLICATION_PATH.'/jqgrid/action_beforeSave.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class action_ver_beforeSave extends action_beforeSave{
	protected $ver_table = '';
	protected $link_table = '';
	protected $link_field = '';
	protected $prj_table = '';
	
	protected function init(&$controller){
		parent::init($controller);
		$this->setHelpTable();
	}
	
	public function setParams($params){
		parent::setParams($params);
		$this->setHelpTable();
	}
	
	protected function setHelpTable($helpTable = array()){
// print_r($this->params);	
		if (empty($helpTable['verTable']))
			$helpTable['verTable'] = $this->get('table').'_ver';
		if (empty($helpTable['linkTable']))
			$helpTable['linkTable'] = 'prj_'.$helpTable['verTable'];
		if (empty($helpTable['linkField']))
			$helpTable['linkField'] = 'prj_id';
		if (empty($helpTable['prjTable']))
			$helpTable['prjTable'] = 'prj';
		$this->ver_table = $helpTable['verTable'];
		$this->link_table = $helpTable['linkTable'];
		$this->link_field = $helpTable['linkField'];
		$this->prj_table = $helpTable['prjTable'];
	}

	protected function _execute(){
		$errorCode = '';
		$link_ids = implode(',', $this->params[$this->link_field.'s']);
		$t_id = isset($this->params['id']) ? $this->params['id'] : (isset($this->params['node_id']) ? $this->params['node_id'] : 0);
		$ver_id = isset($this->params['ver_id']) ? $this->params['ver_id'] : (isset($this->params[$this->ver_table.'_id']) ? $this->params[$this->ver_table.'_id'] : 0);
// print_r("link_ids = $link_ids, t_id = $t_id, ver_id = $ver_id, {$this->ver_table}, {$this->link_table}\n");
// return;
		$res = $this->tool->query("select * from {$this->ver_table} where id=$ver_id");
		if ($ver = $res->fetch()){
			if ($ver['edit_status_id'] == EDIT_STATUS_PUBLISHED || $ver['edit_status_id'] == EDIT_STATUS_GOLDEN){
				$sql = "SELECT ver.*, GROUP_CONCAT(DISTINCT prj.name) as prj_name".
					" FROM {$this->ver_table} ver left join {$this->link_table} link on ver.id=link.{$this->ver_table}_id".
					" left join {$this->prj_table} prj on prj.id=link.{$this->link_field}".
					" WHERE ver.{$this->get('table')}_id=$t_id AND ver.id != $ver_id AND link.{$this->link_field} in ($link_ids)".
					" AND ver.edit_status_id=".EDIT_STATUS_EDITING." AND ver.updater_id=".$this->userInfo->id.
					" GROUP BY ver.id";
// print_r($sql);
// return;
				$res = $this->tool->query($sql);
				$rows = array();
				while($row = $res->fetch()){
					$rows[] = $row;
				}
				if(!empty($rows)){
					$this->renderView('cover_version.phtml', array('rows'=>$rows), '/jqgrid');
				}
			}
		}
		return $errorCode;
	}
}
?>