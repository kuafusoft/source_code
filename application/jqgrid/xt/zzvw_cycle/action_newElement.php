<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_newElement.php');
class xt_zzvw_cycle_action_newElement extends action_newElement{
	public function handleGet(){
		$options = $this->getOptions(false);
		$cols = $options['edit'];
		$this->renderView('newElement.phtml', array('cols'=>$cols), '/jqgrid'); 
	}
}

?>