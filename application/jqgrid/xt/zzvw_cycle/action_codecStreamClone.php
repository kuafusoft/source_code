<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_codecStreamClone extends action_jqgrid{
	public function handleGet(){
		$params = $this->parseParams();
		//clone只针对一个cycle，所以id只有一个，不用搞成字符串的格式
		if (!empty($params['id'])){			
			$sql ="SELECT id, name FROM testcase_priority";
			$res = $this->tool->query($sql);
			$tc_pr = array();
			$tc_pr['all'] = 'All';
			while($info = $res->fetch()){
				$tc_pr[$info['id']] = $info['name'];
			}
			
			$sql ="SELECT `id`, `name` FROM `tag` WHERE `db_table`='xt.codec_stream'";
			$res = $this->tool->query($sql);
			$tag = array();
			$tag[0] = "";
			while($info = $res->fetch()){
				$tag[$info['id']] = $info['name'];
			}
			$cols = array(
				array('name'=>'stream_priority', 'label'=>'Stream Priority', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'checkbox', 'editoptions'=>array('value'=>$tc_pr), 'defval'=>'all'),
				array('name'=>'tag', 'label'=>'Tag', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$tag), 'defval'=>'all')
			);
			$this->renderView('addtional_clone.phtml', array('cols'=>$cols, 'fors'=>'codec'));
		}	
	}
	
}
?>