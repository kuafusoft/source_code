<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_zzvw_cycle_detail_for_report3_action_list extends action_list{
	protected function specialSql($special, &$ret){
// print_r($special);
		foreach($special as $v){
			if(empty($v))
				continue;
			switch($v['field']){
				case 'testcase_id':
					$str_testcase_ids = implode(',', $v['value']);
					$ret['where'] .= " AND testcase_id IN ({$str_testcase_ids})";
					break;
				case 'finish_time_from':
					$ret['where'] .= " AND finish_time>='{$v['value']}'";
					break;
				case 'finish_time_to':
					$ret['where'] .= " AND finish_time<='{$v['value']}'";
					break;
				case 'prj_ids':
					$str_prj_ids = implode(',', $v['value']);
					$ret['where'] .= " AND prj_id IN ($str_prj_ids)";
			}
		}
print_r($ret);	
	}
}

?>