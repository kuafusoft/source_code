<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class qygl_pici_action_list extends action_list{
	protected function getUnknownInfoForRow($row, $fields){
// print_r($fields);		
// print_r($row);				
		foreach($fields as $field){
			switch($field){
				case 'detail':
					$sql = "SELECT * FROM pici_detail WHERE pici_id={$row['id']}";
					$res = $this->tool->query($sql);
					$row['detail'] = $res->fetchAll();
					break;
			}
		
		}
		return $row;
	}
}

?>