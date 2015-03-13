<?php
require_once('input.php');

class cell_submit extends input{
	protected function init($type, $params){
		parent::init($type, $params);
		$this->params['tag'] = 'input';
	}
}
?>