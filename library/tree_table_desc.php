<?php
require_once('table_desc.php');

class tree_table_desc extends table_desc{
	protected function singleColModel($key, $column, $displayField = '', $params = array()){
		$colModel = parent::singleColModel($key, $column, $displayField, $params);
		if($key == 'pid'){
			$value = array(0=>'NO PARENT');
			$defaultValue = 0;
			if (!empty($params['parent']))
				$defaultValue = $params['parent'];
			else if (!empty($params['filters'])){
				$filters = json_decode($params['filters'], true);
				$rules = $filters['rules'];
				foreach($rules as $rule){
					if ($rule['field'] == 'pid'){
						$defaultValue = $rule['data'];
						break;
					}
				}
			}
			$res = $this->db->query("select name from ".$this->get('table')." WHERE id=$defaultValue");
			if($row = $res->fetch()){
				$value[$defaultValue] = $row['name'];
			}
			$colModel['editoptions']['value'] = $value;
			$colModel['editoptions']['defaultValue'] = $defaultValue;
		}
		return $colModel;
	}
}
