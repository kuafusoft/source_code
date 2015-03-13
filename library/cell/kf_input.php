<?php
require_once("kf_cell.php");

class cell_input extends kf_cell{
	protected function init($params){
		parent::init($params);
		$this->params['tag'] = 'input';
	}
}

?>