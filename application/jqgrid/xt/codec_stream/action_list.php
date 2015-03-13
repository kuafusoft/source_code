<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_codec_stream_action_list extends action_list{
	protected function specialSql($special, &$ret){
// print_r($this->special);
// print_r($ret);
		foreach($this->special as $special){
			switch($special['field']){
				case 'key':
					$ret['where'] .= " AND ".$this->tool->generateLeafWhere(array('field'=>'xt.codec_stream.code,xt.codec_stream.name', 'op'=>'like', 'value'=>$special['value']));
					break;
			}
		}
		$ret['main']['from'] .= " LEFT JOIN codec_stream_ver on codec_stream.id=codec_stream_ver.codec_stream_id LEFT JOIN codec_stream_ver_prj on codec_stream.id=codec_stream_ver_prj.codec_stream_ver_id";
		$ret['main']['fields'] .= ", group_concat(DISTINCT codec_stream_ver.id) as ver_ids, ".
				" group_concat(DISTINCT codec_stream_ver_prj.prj_id) as prj_ids, ".
				" GROUP_CONCAT(distinct codec_stream_ver_prj.codec_stream_ver_id) as linked_ver_ids";
		$ret['group'] = 'codec_stream.id';
		
	}
	
	public function getMoreInfoForRow($row){
		if(!empty($row['linked_ver_ids']))
			$row['ver_ids'] = $row['linked_ver_ids'];
		
		// $sql = "SELECT ".
			// " group_concat(distinct auto_level_id) as auto_level_ids, ".
			// " group_concat(distinct testcase_priority_id) as testcase_priority_ids, ".
			// " group_concat(distinct owner_id) as owner_ids,".
			// " group_concat(distinct command separator '\\n') as command".
			// " from testcase_ver".
			// " WHERE id in ({$row['ver_ids']})";
		// $res = $this->db->query($sql);
		// $rr = $res->fetch();
		// $row = array_merge($row, $rr);
		return $row;
	}
	
	protected function filterParams(){
		$params = parent::filterParams();
//print_r($params['searchConditions']);		
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'key':
					$v['field'] = 'name, code';
					$v['op'] = 'like';
					break;
			}
		}
		return $params;
	}
}

?>