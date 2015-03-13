<?php
require_once('action_jqgrid.php');

class qygl_zjzh_action_getpj extends action_jqgrid{ //得到票据列表
	protected function handlePost(){
// print_r($this->params);
		$ret = array();
		$res = $this->tool->query("SELECT zj_pj.* FROM zj_pj left join zjzh on zj_pj.zj_fl_id=zjzh.zj_fl_id WHERE zjzh.id={$this->params['value']} AND to_yw_id=0");
		while($row = $res->fetch()){
			$row['name'] = $row['code']."[总金额:{$row['total_money']}元]";
			$row['title'] = "备注：".$row['note'];
			unset($row['note']);
			$ret[] = $row;
		}
// print_r($ret);
		return json_encode($ret);
	}
}

?>