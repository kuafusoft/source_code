<?php
require_once('action_jqgrid.php');
class useradmin_users_action_getemailpostfix extends action_jqgrid{
	protected function handlePost(){
// print_r($this->params)	;
		$res = $this->tool->query("SELECT * FROM company WHERE id=".$this->params['id']);
		$company = $res->fetch();
		return $company['email_postfix'];
	}
}
?>