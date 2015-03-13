<?php
require_once('action_jqgrid.php');

class xt_zzvw_cycle_detail_stream_action_getStreamType extends action_jqgrid{
	public function handlePost(){
		$params = $this->parseParams();
		$sql = "SELECT DISTINCT codec_stream_type.id as id, codec_stream_type.name as name FROM cycle_detail".
			" LEFT JOIN codec_stream ON cycle_detail.codec_stream_id=codec_stream.id".
			" LEFT JOIN codec_stream_type ON codec_stream.codec_stream_type_id=codec_stream_type.id";
		$where = "1";
		if(!empty($params['value']) && $params['value']){
			$where = "cycle_detail.cycle_id=".$params['value'];
		}
		$where .= " AND codec_stream_type.name is not null";
		$sql .= " WHERE $where ORDER BY codec_stream_type.name ASC";
		$res = $this->tool->query($sql);
		return json_encode($res->fetchAll());
	}
}