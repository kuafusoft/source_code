<?php
require_once('action_jqgrid.php');

class action_getfieldvalue extends action_jqgrid{
	protected function handlePost(){
// print_r($this->params);
		$ret = array();
		if(!empty($this->params['value'])){
			$sql = "SELECT {$this->params['fields']} FROM `{$this->get('table')}` where {$this->params['field']}={$this->params['value']}";
			$res = $this->tool->query($sql);
			if($row = $res->fetch()){
				$ret = $row;
			}
		}
		return json_encode($ret);
	}
}

?>