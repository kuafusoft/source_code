<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getCycleStreamActions extends action_jqgrid{
	public function handleGet(){
		$params = $this->parseParams();
		$cols = array();
		$sql = "select prj_id from cycle where id = ".$params['id'];
		$res = $this->db->query($sql);
		$info = $res->fetch();
print_r($info);
		$cart_data = new stdClass;
		$cart_data->filters = '{"groupOp":"AND","rules":[{"field":"testcase_type_id","op":"eq","data":2}, {"field":"testcase_module_id","op":"eq","data":9}, {"field":"prj_id","op":"eq","data":'.$info['prj_id'].'}]}';
		$cols[] = array('id'=>'testcase_id', 'name'=>'testcase_id', 'label'=>'Actions', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>array()), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'testcase', 'cart_data'=>json_encode($cart_data), 'editrules'=>array('required'=>true));
		$res = $this->db->query("SELECT id, name FROM test_env");
		$env = array();
		$env[0] = '';
		while($info = $res->fetch()){
			$env[$info['id']] = $info['name'];
		}
		$cols[] = array('id'=>'test_env_id', 'name'=>'test_env_id', 'label'=>'Test Env', 'editable'=>true, 'DATA_TYPE'=>'text', 'editoptions'=>array('value'=>$env), 'type'=>'select', 'editrules'=>array('required'=>true));
		$this->renderView('newElement.phtml',  array('cols'=>$cols), '/jqgrid');
	}
	
}
?>