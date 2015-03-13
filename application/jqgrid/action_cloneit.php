<?php
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');

class action_cloneit extends action_save{
	protected $orig_id = 0;
	protected function beforeSave($db, $table, &$pair){
		$this->orig_id = $this->params['id'];
		$this->params['id'] = null;
		return parent::beforeSave($db, $table, $pair);
	}
}
?>