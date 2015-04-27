<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');
require_once('const_def_qygl.php');

class qygl_wz_action_list extends action_list{
	protected $unit_name = array();
	public function setParams($params){
		parent::setParams($params);
		$res = $this->db->query("SELECT * FROM unit");
		while($row = $res->fetch())
			$this->unit_name[$row['id']] = $row['name'];
	}
	
	protected function getUnknownInfoForRow($row, $field){
		if($field == 'unit_name')
			$row[$field] = $this->unit_name[$row['unit_id']];
		return $row;
	}
}

?>