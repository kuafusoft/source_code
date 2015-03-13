<?php
require_once('action_jqgrid.php');
class xt_zzvw_cycle_action_getFormat extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$data = array();
		switch($params['value']){
			case 9://mqx
				$data[0]['id'] = 5;
				$data[0]['name'] = 'zip';
				break;
			case 7://android jb42--codec
				$data[0]['id'] = 2;
				$data[0]['name'] = 'excel';
				break;
			case 11://psdk
				// $data[0]['id'] = 3;
				// $data[0]['name'] = 'yml';
				$data[1]['id'] = 4;
				$data[1]['name'] = 'html';
				break;
			default:
				break;
		}
		if(!empty($data))
			return $data;
	}
}
?>