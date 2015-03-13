<?php
require_once('/cell/text.php');

class kf_password extends kf_text{
	protected function oneView($value, $props){
		$value = '********';
		return parent::oneView($value, $props);
	}
}
?>