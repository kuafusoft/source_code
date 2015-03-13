<?php

require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_action_crInfo extends action_jqgrid{

	public function handleGet(){
		$params = $this->parseParams();
		//添加view
		if (!empty($params['id'])){				
			$cols = array(
				array('name'=>'cq_password', 'label'=>'CQ Password', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'password'),
				array('name'=>'cr_headline', 'label'=>'CR Headline', 'editable'=>true, 'DATA_TYPE'=>'text', 'type'=>'text'),
				array('name'=>'cr_description', 'label'=>'CR Description', 'editable'=>true, 'DATA_TYPE'=>'text','type'=>'textarea')
			);
			$this->renderView('cr_info.phtml', array('cols'=>$cols));
		}	
	}
	
}

?>