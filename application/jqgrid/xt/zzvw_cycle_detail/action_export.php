<?php
require_once(APPLICATION_PATH.'/jqgrid/action_export.php');

class xt_zzvw_cycle_detail_action_export extends action_export{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/xt/'.$this->get('table');
		if(!empty($this->params['parent'])){
			$sql = "select cycle.group_id, os.name as os from cycle".
				" left join prj on prj.id = cycle.prj_id".
				" left join os on os.id = prj.os_id".
				" where cycle.id=".$this->params['parent'];
			$res = $this->tool->query($sql);
			if($info = $res->fetch()){
				$view_params['group_id'] = $info['group_id'];
				$view_params['os'] = 'os_'.strtolower($info['os']);
				if(stripos($view_params['os'], 'android') !== false)
					$view_params['os'] = 'android';
				if(stripos($view_params['os'], 'linux') !== false)
					$view_params['os'] = 'linux';
			}
//print_r($view_params['os']);
		}
		return $view_params;
	}
}

?>