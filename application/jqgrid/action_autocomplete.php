<?php
require_once('action_jqgrid.php');

class action_autocomplete extends action_jqgrid{
	protected function _execute(){
		if (empty($this->params['rows']))
			$this->params['rows'] = 20;
		$length = strlen($this->params['term']) + 6;
		$sql = "SELECT distinct left(`{$this->params['field']}`, $length) as code FROM `{$this->get('table')}` WHERE `{$this->params['field']}` like '{$this->params['term']}%' ";
		if (isset($this->params['creater_id'])){
			if ($this->params['creater_id'] == 'current')
				$sql .= " AND creater_id=".$this->userInfo->id;
			else
				$sql .= " AND creater_id=".$this->params['creater_id'];
		}
		$sql .= " limit 0, {$this->params['rows']}";
		$res = $this->tool->query($sql);
		$ret = array();
		while($row = $res->fetch())
			$ret[] = $row['code'];
		return json_encode($ret);
	}
}
?>