<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_zzvw_cycle_detail_action_list extends action_list{

	protected function handlePost(){
		$this->config();
		$this->params = $this->filterParams();
		$history = $result = false;
		foreach($this->params['searchConditions'] as $k=>$v){
			if($v['field'] == 'testcase_id')
				$history = true;
			else if($v['field'] == 'result_type_id')
				$result = true;
		}
		if($history){
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
		$sqls['main']['fields'] = "`{$this->table_name}`.`id`";
		$limitedSql = $sqls['limit'];
		unset($sqls['limit']);
		$origin_sql = $sqls['where'];
		
        $sql = $this->table_desc->getSql($sqls);
		$res = $this->tool->query($sql);
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
		
		$sql = $this->table_desc->getSql($sqls);
		$res = $this->tool->query($sql);
        $rows = array();
		$sqlKeys = $this->tool->getSqlKeys();
        while($row = $res->fetch()){
			$row['c_f'] = 0;
            $row = $this->table_desc->getMoreInfoForRow($row);
			if (!empty($sqlKeys))
				$row = $this->hilightKeys($row, $sqlKeys);
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
		foreach($params['searchConditions'] as $k=>&$v){
			switch($v['field']){
				case 'key':
					$v['field'] = 'd_code,zzvw_cycle_detail.summary';
					$v['op'] = 'like';
					break;
				case 'result_type_id':
					if ($v['value'] == -1)
						$v['value'] = 0;
					break;
				case 'tester_id':
					if ($v['value'] == -1)
						$v['value'] = 0;
					break;
			}
			$new_add = array('creater_id');
			if(in_array($v['field'], $new_add))
				unset($params['searchConditions'][$k]);
// print_r($v);
		}
		return $params;
	}
}
?>