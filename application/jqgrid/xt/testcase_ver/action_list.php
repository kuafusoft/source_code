<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_testcase_ver_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		foreach($params['searchConditions'] as &$v){
			if (empty($v['field']))
				continue;
			switch($v['field']){
				case 'parent':
					$v['field'] = 'testcase_ver.testcase_id';
					break;
				case 'prj_ids':
					$v['field'] = 'prj_testcase_ver.prj_id';
					$v['op'] = 'IN';
					break;
				case 'updated_from':
					$v['field'] = 'updated';
					$v['op'] = '>=';
					break;
				case 'updated_to':
					$v['field'] = 'updated';
					$v['op'] = '<=';
					break;
			}
		}
//print_r($params);		
		return $params;
	}
}

?>