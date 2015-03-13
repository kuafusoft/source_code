<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_stream_action_getPriority extends action_jqgrid{
		public function handlePost(){
			$params = $this->parseParams();
			$sql =  "SELECT DISTINCT testcase_priority.id as id, testcase_priority.name as name FROM cycle_detail".
				" LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id".
				" LEFT JOIN testcase_priority ON codec_stream.testcase_priority_id=testcase_priority.id";
			$where = "1";
			if(!empty($params['value']) && $params['value']){
				$where = "cycle_detail.cycle_id=".$params['value'];
			}
			$where .= " AND testcase_priority.name is not null";
			$sql .= " WHERE $where ORDER BY testcase_priority.name ASC";
			$res = $this->tool->query($sql);
			return json_encode($res->fetchAll());
		}
	}
?>