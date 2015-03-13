<?php
require_once('table_desc.php');

class xt_env_item extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->parent_field = 'env_item_type_id';
		$this->parent_table = 'env_item_type';
    } 
}
