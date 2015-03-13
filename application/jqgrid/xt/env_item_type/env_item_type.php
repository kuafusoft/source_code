<?php
require_once('table_desc.php');
class xt_env_item_type extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
        $this->options['gridOptions']['subGrid'] = true;
		$this->options['subGrid'] = array('expandField'=>'env_item_type_id', 'db'=>'xt', 'table'=>'env_item');
    }
}
