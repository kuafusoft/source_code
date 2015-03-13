<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class xt_srs_module_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
		foreach($params['searchConditions'] as &$v){
			if ($v['field'] == 'key'){
				$v['field'] = 'code,content';
				$v['op'] = 'like';
			}
		}
		return $params;
	}
}

?>