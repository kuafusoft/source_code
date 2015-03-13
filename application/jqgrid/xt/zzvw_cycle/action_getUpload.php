<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getUpload extends action_jqgrid{
	public function handleGet(){
		$params = $this->parseParams();
		$upload_type = array('', 'case', 'cycle');
		$cols[0] = array('id'=>'upload_type', 'name'=>'upload_type', 'label'=>'Pls Select', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$upload_type), 'editrules'=>array('required'=>true));
		$os[0] = '';
		$sql ="SELECT id, name FROM os WHERE isactive=1";
		$res = $this->db->query($sql);
		while($info = $res->fetch()){
			$os[$info['id']] = $info['name'];
		}
		$cols[1] = array('id'=>'os_id', 'name'=>'os_id', 'label'=>'OS', 'editable'=>true, 'DATA_TYPE'=>'int', 'type'=>'select', 'editoptions'=>array('value'=>$os), 'editrules'=>array('required'=>true));
		if(isset($params['id'])){
			$sql ="SELECT prj.os_id as os_id FROM cycle LEFT JOIN prj ON cycle.prj_id=prj.id WHERE cycle.id=".$params['id'];
			$res = $this->db->query($sql);
			$info = $res->fetch();
			$cols[1]['defval'] = $info['os_id'];
		}
		$format = array('', 'txt', 'excel', 'yml', 'html', 'zip');
		$cols[] = array('id'=>'file_format', 'name'=>'file_format', 'label'=>'Foramt', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$format), 'editrules'=>array('required'=>true));	
		$cols[] = array('id'=>'cycle_logfile', 'name'=>'cycle_logfile', 'label'=>'Report File', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'file', 'editrules'=>array('required'=>true));	
		$this->renderView('upload.phtml', array('cols'=>$cols, 'f'=>'post')); 
	}
	
}
?>