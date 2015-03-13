<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/testcase/action_save.php');

class xt_testcase_action_cloneit extends xt_testcase_action_save{
	protected $orig_id = 0;
	protected function beforeSave($db, $table, &$pair){
		$this->orig_id = $this->params['id'];
		unset($this->params['id']);
		unset($this->params['node_id']);
		unset($this->params['ver_id']);
		
		unset($this->params['update_comment']);
		unset($this->params['review_comment']);
		
		$this->params['ver'] = 1;
		return parent::beforeSave($db, $table, $pair);
	}
}
?>