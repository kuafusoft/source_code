<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getCloneAll extends action_jqgrid{
	public function handleGet(){
		$rel[0] = '';
		$res = $this->db->query("SELECT id, name FROM rel");
		while($row = $res->fetch()){
			$rel[$row['id']] = $row['name'];
		}
		$cols = array(
			array('id'=>'myname', 'name'=>'myname', 'label'=>'My name', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text', 'editrules'=>array('required'=>true)),
			array('id'=>'rel_id', 'name'=>'rel_id', 'label'=>'Rel', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'select', 'editoptions'=>array('value'=>$rel), 'editrules'=>array('required'=>true)),
			array('id'=>'start_date', 'name'=>'start_date', 'label'=>'Start Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true)),
			array('id'=>'end_date', 'name'=>'end_date', 'label'=>'End Date', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'date', 'editrules'=>array('required'=>true))
		);
		$this->renderView('newElement.phtml', array('cols'=>$cols), '/jqgrid'); 
	}
	
}
?>