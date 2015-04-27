<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');
require_once('const_def_qygl.php');

class qygl_dingdan_xs_action_list extends action_list{
	protected $unit_name = array();
	public function setParams($params){
		parent::setParams($params);
		$res = $this->db->query("SELECT * FROM unit");
		while($row = $res->fetch())
			$this->unit_name[$row['id']] = $row['name'];
	}
	
	protected function getUnknownInfoForRow($row, $field){
// print_r($field);		
		if($field == 'unit_name'){
			$res = $this->tool->query("select unit_id from wz where id={$row['wz_id']}");
			$temp = $res->fetch();
			$row[$field] = $this->unit_name[$temp['unit_id']];
		}
		return $row;
	}
}

?>