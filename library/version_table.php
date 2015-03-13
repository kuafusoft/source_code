<?php
require_once('table_desc.php');
require_once('kf_editstatus.php');

class version_table extends table_desc{
	protected $ver_table;
	protected $link_table;
	protected $ver_desc;
	
	public function setVerTable($ver_table, $link_table){
		$this->ver_table = $ver_table;
		$this->ver_desc = tableDescFactory::get($this->get('db'), $this->ver_table);
		$this->link_table = $link_table;
	}
	
	
	/*
		publish可能有两个地方发起:Node发起或Ver发起,区别在于id的含义,一个指向node_id,一个指向ver_id.谁发起在参数from指定,默认为Ver发起
		*/
	public function publish(){
        $params = $this->tool->parseParams('publish');
		if (empty($params['from']))$params['from'] = 'ver';
		if ($params['from'] == 'node'){
			$vers = $this->getVerIds($params);
		}
		else
			$vers = json_decode($params['element']);

		$this->_publish($vers);
	}
	
	protected function getVerIds($params){
		$edit_status = implode(',', array(EDIT_STATUS_EDITING, EDIT_STATUS_REVIEW_WAITING, EDIT_STATUS_REVIEWING, EDIT_STATUS_REVIEWING));
	
	}
	
	protected function _publish($vers){
		$strVerIds = implode(',', $vers);
		$this->db->update($this->get('ver_table'), array('edit_status_id'=>EDIT_STATUS_PUBLISH), "id in ($strVerIds)");
		$link_table = $this->options['link_table'];
		if (!empty($link_table)){
			$this->_publishLinkTable($vers);
		}
	}
	
	protected function _publishLinkTable($vers){
		$strVerIds = implode(',', $vers);
		$this->db->update($this->get('link_table'), array('edit_status_id'=>EDIT_STATUS_PUBLISH), $this->get('link_table').".id in ($strVerIds)");
	}
	
    protected function _saveOne($db, $table, $pair){
		$node_id = $this->saveNode($db, $table, $pair);
		$ver_id = $this->saveVer($db, $this->get('ver_table'), $pair, $node_id);
		$this->afterSaveOne($db, $table, $pair, $ver_id);
		return $ver_id;
	}
	
	protected function afterSaveOne($db, $table, $pair, $ver_id){// 处理一些额外的写入
	
	}
	
	protected function saveNode($db, $table, $pair){
		$node = $this->prepareNodeData($db, $table, $pair);
		$node_id = parent::_saveOne($db, $table, $node);
		return $node_id;
	}
	
	protected function saveVer($db, $ver_table, $pair, $node_id){
		$ver = $this->prepareVerData($db, $ver_table, $pair, $node_id);
		$lastVer = 0;
		if($this->needNewVer($ver)){
			$lastVer = $ver['id'];
			unset($ver['id']);
			$ver['ver'] = $this->getNextVer($db, $this->options['ver_table'], $node_id);
			$ver['edit_status_id'] = EDIT_STATUS_EDITING;
		}
		$ver_id = parent::_saveOne($db, $ver_table, $ver);
			
		if (!empty($this->options['link_table'])){
			$link_id = $this->saveLink($db, $this->options['link_table'], $pair, $node_id, $ver_id);
		}
		return $ver_id;
	}
	
	protected function saveLink($db, $table, $pair, $node_id, $ver_id){
		return 0;
	}
	
	protected function prepareNodeData($db, $table, $pair){
		$node = $this->tool->extractData($pair, $this->get('table'), $this->get('db'));
		$node['id'] = $pair['node_id'];
		return $node;
	}
	
	protected function prepareVerData($db, $ver_table, $pair, $node_id){
		$ver = $this->tool->extractData($pair, $ver_table, $this->get('db'));
		$ver[$this->get('table').'_id'] = $node_id;
		$ver['id'] = $pair['ver_id'];
/*		
		if (!isset($pair[$this->get('ver_table').'_id']))
			$ver['id'] = 0;
		else
			$ver['id'] = $pair[$this->get('ver_table').'_id'];
*/			
		if (!isset($pair['edit_status_id']))
			$ver['edit_status_id'] = EDIT_STATUS_EDITING;
		if (empty($pair['ver']))
			$ver['ver'] = 1;
		return $ver;
	}

	protected function needNewVer($ver){
		$needNewVer = false;
		if (empty($ver['id']) || $ver['edit_status_id'] == EDIT_STATUS_PUBLISHED || $ver['edit_status_id'] == EDIT_STATUS_GOLDEN){ // need new version
			$needNewVer = true;
		}
		return $needNewVer;
	}
	
	private function getNextVer($db, $ver_table, $node_id){
		$nextVer = 1;
		$res = $this->db->query("select max(ver) as max_ver FROM $ver_table WHERE {$this->options['table']}_id=$node_id");
		if ($row = $res->fetch())
			$nextVer = $row['max_ver'] + 1;
		return $nextVer;
	}
}
