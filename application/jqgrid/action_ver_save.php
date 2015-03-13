<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
/*
保存前应先检查是否已经有自己创建的处于非published状态的且关联的project一致的Version存在，如果已经存在，则应询问是否覆盖到该Version

*/
class action_ver_save extends action_save{
	protected $ver_table = '';
	protected $link_table = '';
	protected $link_field = 'prj_id';
	
	protected function init(&$controller){
		parent::init($controller);
		$this->setHelpTable();
	}
	
	protected function setHelpTable($helpTable = array()){
		if (empty($helpTable['verTable']))
			$helpTable['verTable'] = $this->get('table').'_ver';
		if (empty($helpTable['linkTable']))
			$helpTable['linkTable'] = 'prj_'.$helpTable['verTable'];
		if (empty($helpTable['linkField']))
			$helpTable['linkField'] = 'prj_id';
		$this->ver_table = $helpTable['verTable'];
		$this->link_table = $helpTable['linkTable'];
		$this->link_field = $helpTable['linkField'];
	}
	
	protected function prepare($db, $table, $params){
//print_r($this->params);	
		$pair = parent::prepare($db, $table, $params);
		if ($table == $this->get('table') && isset($params['node_id'])){
			$pair['id'] = $params['node_id'];
		}
		else if ($table == $this->ver_table){
			if (isset($params['ver_id']))
				$pair['id'] = $params['ver_id'];
			if (!empty($params['cover_ver_id'])){
				$pair['id'] = $params['cover_ver_id'];
				$pair['edit_status_id'] = EDIT_STATUS_EDITING;
				// unset the ver
				unset($pair['ver']);
			}
			else{
				if(empty($pair['edit_status_id']))
					$pair['edit_status_id'] = EDIT_STATUS_EDITING;
				$needNewVer = empty($pair['id']);
				if ($pair['edit_status_id'] == EDIT_STATUS_PUBLISHED || $pair['edit_status_id'] == EDIT_STATUS_GOLDEN){
					$needNewVer = true;
				}
				if ($needNewVer){
					$pair['update_from'] = empty($pair['id']) ? 0 : $pair['ver'];
					$res = $this->tool->query("select max(ver) as max_ver FROM {$this->ver_table} WHERE {$this->get('table')}_id=".$params[$this->get('table').'_id']);
					$row = $res->fetch();
					$pair['ver'] = $row['max_ver'] + 1;
					$pair['edit_status_id'] = EDIT_STATUS_EDITING;
					unset($pair['id']);
				}
			}
		}
		return $pair;
	}
	
	protected function save($db, $table, $params){
// print_r($params);
		if($params['cloneit'] == 'true'){
			unset($params['id']);
			unset($params['node_id']);
			unset($params['ver_id']);
			unset($params['update_comment']);
			unset($params['issue_comment']);
			unset($params['review_comment']);
			$params['ver'] = 1;
		}
// print_r($params)		;
		$t = $this->prepare($db, $table, $params);
//print_r($t);
		$t_id = parent::_saveOne($db, $table, $t);
		
		$params[$table.'_id'] = $t_id;
		$ver_id = $this->saveVer($db, $params);

		$this->processLink($t_id, $ver_id, $params);
		return $t_id.":".$ver_id;
	}

	protected function processLink($t_id, $ver_id, $params){
		$v_link_field = array();
		if (!empty($params[$this->link_field.'s'])){
			$v_link_field = $params[$this->link_field.'s'];
		}
		$newLink = $v_link_field;
		$removedLink = array();
		$res = $this->tool->query("SELECT GROUP_CONCAT({$this->link_field}) as link_ids FROM {$this->link_table} WHERE {$this->ver_table}_id=$ver_id");
		if ($row = $res->fetch()){
			if (!empty($row['link_ids'])){
				$currentPrjs = explode(',', $row['link_ids']);
				$newLink = array_diff($v_link_field, $currentPrjs);
				$removedLink = array_diff($currentPrjs, $v_link_field);
			}
		}
//print_r($prj_ids);
//print_r($newPrj);
//print_r($removePrj);		
		if (!empty($removedLink)){
			$this->tool->query("DELETE FROM {$this->link_table} WHERE {$this->ver_table}_id=$ver_id AND {$this->link_field} in (".implode(',', $removedLink).")");
		}
		$link = $this->prepareLink($t_id, $ver_id);
		foreach($newLink as $prj_id){
			$link[$this->link_field] = $prj_id;
			$this->tool->insert($this->link_table, $link);
		}
    }
	
	protected function saveVer($db, $params){
		$ver = $this->prepare($db, $this->ver_table, $params);
// print_r($ver);
		$ver_id = parent::_saveOne($db, $this->ver_table, $ver);
		return $ver_id;
	}
	
	protected function prepareLink($t_id, $ver_id){
		$link = array($this->get('table').'_id'=>$t_id, $this->ver_table.'_id'=>$ver_id);
		return $link;
	}
}
?>