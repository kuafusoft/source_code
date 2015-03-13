<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
class action_view_save extends action_save{
	protected $view = array();
	
	protected function init(&$controller){
		parent::init($controller);
		$this->setViewInfo();
	}

	protected function setViewInfo(){ // view = array('link_field'=>$link_table, );
		// here set $this->view
	}
	
	protected function newRecord($db, $table, $pair){
		$this->fillDefaultValues('new', $pair, $db, $table);
		$pair['ht_fl_id'] = HT_FL_LD;
		$pair['ht_xz_id'] = HT_XZ_CHANGQI;
		$hb = $this->tool->extractData($pair, 'hb', $db);
		$hb_id = $this->tool->insert('hb', $hb, $db);
		
		$ht = $this->tool->extractData($pair, 'ht', $db);
		$ht['hb_id'] = $hb_id;
		$ht_id = $this->tool->insert('ht', $ht, $db);
		
		$ht_ld = $this->tool->extractData($pair, 'ht_ld', $db);
		$ht_ld['ht_id'] = $ht_id;
		$ht_ld_id = $this->tool->insert('ht_ld', $ht_ld, $db);
		return $hb_id;
	}
	
	protected function updateRecord($db, $table, $pair, $id = 'id'){
		// 填入一些默认值：updater_id, updated
		$this->fillDefaultValues('update', $pair, $db, $table);
		$hb_id = $pair[$id];
		$ht_id = $pair['ht_id'];
		$ht_ld_id = $pair['ht_ld_id'];
		
		$hb = $this->tool->extractData($pair, 'hb', $db);
		$this->tool->update($table, $hb, '', $db);

		$ht = $this->tool->extractData($pair, 'ht', $db);
		$ht['id'] = $ht_id;
		$this->tool->update('ht', $ht, '', $db);

		$ht_ld = $this->tool->extractData($pair, 'ht_ld', $db);
		$ht_ld['id'] = $ht_ld_id;
		$this->tool->update('ht_ld', $ht_ld, '', $db);
		// $this->db->update($db.'.'.$table, $pair, $id.'='.$pair[$id]);
		return $hb_id;
	}
}
?>