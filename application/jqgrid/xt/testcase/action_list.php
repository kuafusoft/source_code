<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_testcase_action_list extends action_list{
	protected function specialSql($special, &$ret){
		$prj_filter = array();
		$prj_where = '1';
		$prj_empty = false;
		foreach($special as $c){
			switch($c['field']){
				case 'prj_id':
					$prj_filter = $c;
					break;
				case 'os_id':
				case 'chip_id':
				case 'board_type_id':
					$prj_where .= ' AND '.$this->tool->generateLeafWhere($c);
					break;
				case 'key':
					$ret['where'] .= ' AND '.$this->tool->generateLeafWhere(array('field'=>'xt.testcase.code,xt.testcase.summary', 'op'=>'like', 'value'=>$c['value']));
					break;
				default:
					$c['field'] = 'testcase_ver.'.$c['field'];
					$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
			}
		}
		$v = array();
		if(!empty($prj_filter)){
			$v = $prj_filter['value'];
			if(is_string($v))
				$v = explode(',', $v);
			else if (is_int($v))
				$v = array($v);
		}
		elseif($prj_where != '1'){
			$res = $this->tool->query("SELECT id FROM prj WHERE $prj_where");
			while($row = $res->fetch()){
				$v[] = $row['id'];
			}
			if(empty($v))
				$prj_empty = true;
		}
		if(!empty($v)){
			$ret['where'] .= ' AND '.$this->tool->generateLeafWhere(array('field'=>'xt.prj_testcase_ver.prj_id', 'op'=>'IN', 'value'=>$v));
		}
		$ret['main']['from'] .= " LEFT JOIN prj_testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id";
		$ret['main']['fields'] .= ", group_concat(DISTINCT testcase_ver.id) as ver_ids, ".
				" group_concat(DISTINCT prj_testcase_ver.prj_id) as prj_ids, ".
				" GROUP_CONCAT(distinct prj_testcase_ver.testcase_ver_id) as linked_ver_ids";
	}

	public function getMoreInfoForRow($row){
		if(!empty($row['linked_ver_ids']))
			$row['ver_ids'] = $row['linked_ver_ids'];
		
		return $row;
	}

	protected function filterParams(){
		$params = parent::filterParams();
		$searchConditions = $params['searchConditions'];
// print_r($this->special);		
//print_r($params['searchConditions']);		
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'xt.testcase.last_run':
					$v['op'] = '<=';
					break;
			}
			
		}
// print_r($params);		
		return $params;
	}
}

?>