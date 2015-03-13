<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/action_list.php');

class xt_zzvw_subgrid_cycle_detail_action_list extends xt_zzvw_cycle_detail_action_list{
	protected function handlePost(){
		return $this->getlist("", 2);
	}

	protected function getlist($special, $c_f){
		$detail = '';//subgrid detail element
		$ret = array();
		$this->config();
		$this->params = $this->filterParams();	
		foreach($this->params['searchConditions'] as $k=>$v){
			if(isset($v['field']) && ($v['field'] == 'id')){
				$res = $this->db->query("SELECT id, cycle_id, codec_stream_id, test_env_id FROM cycle_detail WHERE id=".$v['value']);
				if($detail = $res->fetch()){
					$special = " AND (cycle_id=".$detail['cycle_id'].")".
						" AND (codec_stream_id=".$detail['codec_stream_id'].")".
						" AND (test_env_id=".$detail['test_env_id'].")";
					unset($this->params['searchConditions'][$k]);
				}
			}
		}
        $rownum = $this->params['limit']['rows'];
		if ($rownum == 0)
			$rownum = 'ALL';
        $cookie = array('type'=>'rowNum', 'name'=>$this->db_name.'_'.$this->table_name, 'content'=>json_encode(array('rowNum'=>$rownum)));
        $this->saveCookie($cookie);
		$sqls = $this->table_desc->calcSqlComponents($this->params, true);
// print_r($sqls);
		$mainFields = $sqls['main']['fields'];
		$sqls['main']['fields'] = "`{$this->table_name}`.`id`";
		$limitedSql = $sqls['limit'];
		unset($sqls['limit']);
		$origin_sql = $sqls['where'];
		//falg=0时，group by codec_stream_id; flag=1时，显示codec_stream_id下的trickmode cases
		$sqls['where'] .= $special;
		
        $sql = $this->table_desc->getSql($sqls);
		$res = $this->db->query($sql);
		$ret['records'] = $res->rowCount();
		$res->closeCursor();
		
        $ret['page'] = $this->params['page'];
        if ($this->params['limit']['rows'] > 0)
            $ret['pages'] = ceil($ret['records'] / $this->params['limit']['rows']);
		else
			$ret['pages'] = 1;

		$sqls['main']['fields'] = $mainFields;
		$sqls['where'] = $origin_sql;
		$sqls['limit'] = $limitedSql;
		$sqls['where'] .= $special;
		
		$sql = $this->table_desc->getSql($sqls);
		$res = $this->db->query($sql);
        $rows = array();
		$sqlKeys = $this->tool->getSqlKeys();
        while($row = $res->fetch()){
			$row['c_f'] = $c_f;
            $row = $this->table_desc->getMoreInfoForRow($row);
			if (!empty($sqlKeys))
				$row = $this->hilightKeys($row, $sqlKeys);
            $rows[] = $row;
        }
		$res->closeCursor();
        $ret['rows'] = $rows;
        $ret['sql'] = $sql;
		$ret['keys'] = $sqlKeys;
		return $ret;
	}
}
?>