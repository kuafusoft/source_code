<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_list.php');

class xt_zzvw_cycle_detail_stream_action_list extends xt_zzvw_cycle_detail_action_list{

	protected function handlePost(){
		$this->config();
		$table = $this->get('table');
		$this->params = $this->filterParams();
		$history = $result = false;
		foreach($this->params['searchConditions'] as $k=>$v){
			if($v['field'] == 'codec_stream_id')
				$history = true;
			else if($v['field'] == 'result_type_id')
				$result = true;
		}
		if($history && !$result){
			if(!$result){
				$this->params['searchConditions'][$k+1]['field'] = 'result_type_id';
				$this->params['searchConditions'][$k+1]['op'] = '!=';
				$this->params['searchConditions'][$k+1]['value'] = '0';
			}
			if(empty($this->params['order']))
				$this->params['order'] = 'created desc';
		}
        $ret = array();
        $rownum = $this->params['limit']['rows'];
		if ($rownum == 0)
			$rownum = 'ALL';
        $cookie = array('type'=>'rowNum', 'name'=>$this->db_name.'_'.$this->table_name, 'content'=>json_encode(array('rowNum'=>$rownum)));
        $this->saveCookie($cookie);
		$sqls = $this->table_desc->calcSqlComponents($this->params, true);
		$mainFields = $sqls['main']['fields'];
		$origin_sql = $sqls['where'];
		$limitedSql = $sqls['limit'];	
		unset($sqls['limit']);	
		if($this->params['result_type_id'] == '-1')
			$this->params['result_type_id'] = 0;
// print_r($this->params['result_type_id']);
		if($this->params['result_type_id'] != 2 && $this->params['result_type_id'] != 0){
			if(!$history)
				$sqls['where'] = $this->table_name.".cycle_id = ".$this->params['parent'];			
			$sql = $this->table_desc->getSql($sqls);
// print_r($sql."\n");
			$res = $this->tool->query($sql);
			while($info = $res->fetch()){
				if(!isset($stream[$info['codec_stream_id']]))
					$stream[$info['codec_stream_id']] = $info['codec_stream_id'];
				if($info['result_type_id'] != $this->params['result_type_id']){
					if(!isset($record[$info['codec_stream_id']])){
						$record[$info['codec_stream_id']] = $info['codec_stream_id'];
					}
				}
			}
			if(!empty($record))
				$ret['records'] = count($stream) - count($record);
			else
				$ret['records'] = count($stream);
		}
		else{
			$sqls['main']['fields'] = $this->table_name.".codec_stream_id, ".$this->table_name.".result_type_id";
			$sqls['group'] = $table.".cycle_id, ".$table.".codec_stream_id, ".$table.".test_env_id";
			$sql = $this->table_desc->getSql($sqls);
			$res = $this->tool->query($sql);
			$ret['records'] = $res->rowCount();	
			$res->closeCursor();
		}	
// print_r($ret['records']."\n");		
        $ret['page'] = $this->params['page'];
        if ($this->params['limit']['rows'] > 0)
            $ret['pages'] = ceil($ret['records'] / $this->params['limit']['rows']);
		else
			$ret['pages'] = 1;

		$sqls['main']['fields'] = $mainFields;
		$sqls['where'] = $origin_sql;	
		$sqls['limit'] = $limitedSql;
		$sqls['group'] = $table.".cycle_id, ".$table.".codec_stream_id, ".$table.".test_env_id";
		// $sqls['where'] .= $special;
		
		$sql = $this->table_desc->getSql($sqls);
		$res = $this->tool->query($sql);
        $rows = array();
		$sqlKeys = $this->tool->getSqlKeys();
        while($row = $res->fetch()){
			$row['c_f'] = 1;
            $row = $this->table_desc->getMoreInfoForRow($row);
			if (!empty($sqlKeys))
				$row = $this->hilightKeys($row, $sqlKeys);
			if(isset($record)){
				if(!in_array($row['codec_stream_id'], $record))
					$rows[] = $row;
			}
			else
				$rows[] = $row;
        }
		$res->closeCursor();
		
        $ret['rows'] = $rows;
        $ret['sql'] = $sql;
		$ret['sqls'] = json_encode($sqls);
		$ret['keys'] = $sqlKeys;
		return $ret;
	}
	
	protected function filterParams(){
		$params = parent::filterParams();
//print_r($params['searchConditions']);		
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'd_code,zzvw_cycle_detail.summary':
					$v['field'] = 'name,zzvw_cycle_detail_stream.d_code';
					$v['op'] = 'like';
					break;
			}
// print_r($v);
		}
		return $params;
	}
}
?>