<?php

require_once('table_desc.php');

class xt_test_env extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id', 
			'name',
			'env_item_ids'=>array('hidden'=>true),
			'creater_id',
			'created',
			'isactive',
		);
    } 
/*	
    public function getMoreInfoForRow($row){
		if (!empty($row['env_item_ids'])){
			$res = $this->db->query("SELECT group_concat(code) as env_item FROM env_item WHERE id in ({$row['env_item_ids']})");
			$cc = $res->fetch();
			$row['env_item'] = $cc['env_item'];
		}
		return $row;
	}
*/	
}
