<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_action_cycle_cases extends action_jqgrid{

	public function handleGet(){
		$data = '';
		$data['prj_id'] = '';
		$params = $this->parseParams();
		if(isset($params['id'])){
			$res = $this->db->query("SELECT prj_id FROM cycle WHERE id=".$params['id']);
			$data = $res->fetch();
		}
		$cols = array(
			array('name'=>'code', 'label'=>'Testcase', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
			//array('name'=>'id', 'label'=>'Test Cycle', 'query=>true', 'editable'=>false, 'DATA_TYPE'=>'text', 'type'=>'select'),
			array('name'=>'prj_id', 'label'=>'Prj', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'queryoptions'=>array('value'=>$data['prj_id']), 'searchoptions'=>array('value'=>'')),
			array('name'=>'testcase_type_id', 'label'=>'Cycle Type', 'query'=>'true', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')), //'editrules'=>array('required'=>true)),
			//array('name'=>'myname', 'label'=>'result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'text'),
			array('name'=>'compiler_id', 'label'=>'Compiler', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'build_target_id', 'label'=>'Build Target', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'cycle_id', 'label'=>'Cycle', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'testcase_module_id', 'label'=>'Module', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'testcase_testpoint_id', 'label'=>'Testpoint', 'query'=>'true',  'editable'=>false, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'auto_level_id', 'label'=>'Auto Level', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'result_type_id', 'label'=>'Result', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'tester_id', 'label'=>'Tester', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'searchoptions'=>array('value'=>'')),
			array('name'=>'defect_ids', 'label'=>'CR', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text','type'=>'text', 'searchoptions'=>array('value'=>'')),
			array('name'=>'testcase_priority_id', 'label'=>'Priority', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'cols'=>6, 'queryoptions'=>array('value'=>'1,2,3'), 'searchoptions'=>array('value'=>'')),
			array('name'=>'codec_stream_priority', 'label'=>'Stream Priority', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'cols'=>6, 'searchoptions'=>array('value'=>''), 'queryoptions'=>array('advanced'=>true)),
			array('name'=>'codec_stream_name', 'label'=>'Stream', 'query'=>'true',  'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'queryoptions'=>array('advanced'=>true)),
			
		);
		foreach($cols as $row=>$search){
			if($search['name'] != 'defect_ids' && $search['name'] != 'code' && $search['name'] != 'stream_priority'){
				if (preg_match('/^(.+)_(ids?)$/i', $search['name'], $matches))
					$data = $this->getdata($matches[1]);
			}
			
			foreach($search as $key=>$val){
				if(!empty($data) && ($key == 'searchoptions')){
					$cols[$row]['searchoptions']['value'] = $data;
					unset($data);
				}
			}
			if($search['name'] == 'codec_stream_priority'){
				$res = $this->db->query("SELECT id, name FROM testcase_priority");
				while($info = $res->fetch())
					$priority[$info['id']] = $info['name'];
				$cols[$row]['searchoptions']['value'] = $priority;
			}
			if(!empty($search['query'])){
				if (!empty($search['queryoptions']['advanced']))
					$advanced[] = $cols[$row];
				else
					$normal[] = $cols[$row];
			}
		}
		$query = array('advanced'=>$advanced, 'normal'=>$normal);
		
		$options = array('query'=>$query, 'container'=>$params['container'], 'db'=>'xt', 'table'=>'zzvw_cycle_detail', 'buttonFlag'=>false);
		$this->renderView('other_cycle_case.phtml', array('options'=>$options, 'container'=>$params['container'], 'db'=>'xt', 'table'=>'zzvw_cycle_detail'));	      
	}
	
	private function getdata($table){
		if($table == 'tester'){
			$data = $this->userAdmin->getUserList(true);
		}else{
			$data['0'] = '';
			$sql = "SELECT id, name FROM $table";
			if($table == "cycle")
				$sql .= " WHERE cycle_status_id = 1";
			$res = $this->db->query($sql);
			while($info = $res->fetch()){
				$data[$info['id']] = $info['name'];
			}
		}
		return $data;
	}
}
?>