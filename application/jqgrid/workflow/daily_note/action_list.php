<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class workflow_daily_note_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		$addCond = array();
//print_r($params['searchConditions']);		
		foreach($params['searchConditions'] as $i=>&$v){
			switch($v['field']){
				case 'key':
					$v['field'] = 'content';
					$v['op'] = 'like';
					break;
				case 'who':
					$v['field'] = 'creater_id';
					$v['op'] = '=';
					switch($v['value']){
						case 'myself':
							$v['value'] = $this->userInfo->id;
							break;
						case 'all':
							unset($params['searchConditions'][$i]);
							break;
					}
					break;
				case 'work_summary_id':
					$res = $this->db->query("SELECT period.from, period.end ".
						" FROM work_summary LEFT JOIN period on work_summary.period_id=period.id ".
						" WHERE work_summary.id={$v['value']}");
					$row = $res->fetch();
// print_r($row);				
					
					preg_match('/(.*)-(.*)-(.*)$/', $row['from'], $from);
					preg_match('/(.*)-(.*)-(.*)$/', $row['end'], $end);
// print_r($from);
// print_r($end);					
					$v['field'] = 'created';
					$v['op'] = '>=';
					$v['value'] = date('Y-m-d', mktime(0, 0, 0, $from[2]  , $from[3] - 7, $from[1]));
					$addCond[] = array('field'=>'created', 'op'=>'<=', 'value'=>date('Y-m-d', mktime(0, 0, 0, $end[2]  , $end[3] + 1, $end[1])));
			}
		}
		if (!empty($addCond))
			$params['searchConditions'] = array_merge($params['searchConditions'], $addCond);
		return $params;
	}
}

?>