<?php
require_once('action_jqgrid.php');

class workflow_period_action_batch_gen extends action_jqgrid{
	protected function getViewParams($params){
		$view_params = $params;
		$view_params['view_file'] = "batch_gen.phtml";
		$view_params['view_file_dir'] = '/jqgrid/workflow/period';

		return $view_params;
	}
	
	protected function handlePost(){
		/*
		命名规则：2014 WW08/WM01/WS01/WH01
		*/
		$existedPeriod = array();
		$year = $this->params['year'];
		foreach($this->params['period'] as $period){
			switch($period){
				case 'week':
					$oneWeek = 7 * 24 * 3600;
					$firstDay = sprintf('%4d-01-01', $year);
					list($st, $end) = $this->tool->getWeekStartEndDay($firstDay);
					$st = strtotime($st);
					for($i = 1; $i < 54; $i ++){
						$name = sprintf("%4d WW%02d", $year, $i);
						$from = date('Y-m-d', $st);
						$end = date('Y-m-d', $st + $oneWeek - 1);
						$st = $st + $oneWeek;
						$this->generatePeriod($name, $from, $end, $existedPeriod);
					}
					break;
				case 'month':
					for($i = 1; $i < 13; $i ++){
						$name = sprintf("%4d WM%02d", $year, $i);
						$st = date('Y-m-d', mktime(0, 0, 0, $i, 1, $year));
						$end = date('Y-m-d', mktime(0, 0, 0, $i + 1, 0, $year));
						$this->generatePeriod($name, $st, $end, $existedPeriod);
					}
					break;
				case 'season':
					$st_m = 1;
					for($i = 1; $i < 5; $i ++){
						$name = sprintf("%4d WS%02d", $year, $i);
						$st = date('Y-m-d', mktime(0, 0, 0, $st_m, 1, $year));
						$end = date('Y-m-d', mktime(0, 0, 0, $st_m + 3, 0, $year));
						$this->generatePeriod($name, $st, $end, $existedPeriod);
						$st_m += 3;
					}
					break;
				case 'half_year':
					$st_m = 1;
					for($i = 1; $i < 3; $i ++){
						$name = sprintf("%4d WH%02d", $year, $i);
						$st = date('Y-m-d', mktime(0, 0, 0, $st_m, 1, $year));
						$end = date('Y-m-d', mktime(0, 0, 0, $st_m + 6, 0, $year));
						$this->generatePeriod($name, $st, $end, $existedPeriod);
						$st_m += 6;
					}
					break;
			}
		}
//print_r($existedPeriod);		
		return $existedPeriod;
	}
	
	protected function generatePeriod($name, $from, $end, &$existedPeriod){
		$res = $this->db->query("SELECT * FROM period where name=:name", array('name'=>$name));
		if (!$row = $res->fetch()){
			$this->db->insert('period', compact('name', 'from', 'end'));
		}
		else{
			$existedPeriod[] = compact('name', 'from', 'end');	
		}
	}
	
}

?>