<?php
require_once('exporter_excel.php');

class workflow_work_report_exporter_excel_report extends exporter_excel{
	public function setOptions($jqgrid_action){
		$titles = array(
			array('index'=>'period', 'width'=>100, 'label'=>'Period', 'cols'=>1),
			array('index'=>'creater_id', 'width'=>100, 'label'=>'creater', 'cols'=>1),
		);
		//从item_prop里获取有多少种类，每个种类一列
		$res = $this->tool->query("SELECT * FROM item_prop");
		while($row = $res->fetch()){
			$titles[] = array('index'=>'item_prop_'.$row['id'], 'label'=>$row['name'], 'width'=>355);
		}
		$data = $this->getData(null);
		$this->params['sheets'][0] = array('title'=>'Report', 'startRow'=>2, 'startCol'=>1, 'header'=>array('rows'=>array($titles)), 'data'=>$data);
	}
	
	protected function getData($table_desc, $searchConditions = array(), $order = array()){
		$data = array();
		$prjs = array(0=>'For General');
		$res = $this->tool->query("SELECT id, name FROM prj");
		while($row = $res->fetch()){
			$prjs[$row['id']] = $row['name'];
		}
		$users = array();
		$res = $this->tool->query("SELECT id, username, nickname FROM useradmin.users");
		while($row = $res->fetch()){
			$users[$row['id']] = $row['nickname'].'-'.$row['username'];
		}
		
		$action_list = actionFactory::get(null, 'list', $this->params);
		$list = $action_list->getList();
		$i = 0;
// print_r($list);		
		foreach($list as $report){
// print_r($report);			
			$work_report_detail = $report['work_report_detail'];
			unset($report['work_report_detail']);
			$data[$i] = $report;
			if(!empty($data[$i]['creater_id']))
				$data[$i]['creater_id'] = $users[$data[$i]['creater_id']];
			$detail = array();
			foreach($work_report_detail as $item){
				$detail['item_prop_'.$item['item_prop_id']][$prjs[$item['prj_id']]][] = $item['content'];
			}
// print_r($detail);			
			$str = "";
			$prj_str = "";
			foreach($detail as $item_prop=>$prop_data){
				$prj_str = array();
				foreach($prop_data as $prj=>$prj_data){
					$a_str = array();
					$j = 1;
					foreach($prj_data as $content){
						$a_str[] = $j.":$content";
						$j ++;
					}
					$prj_str[$prj] = $prj.":\n\t".implode("\n\t", $a_str);
				}
				$data[$i][$item_prop] = implode("\n\n", $prj_str);
			}
			$i ++;
		}
// print_r($data);		
		return $data;
	}
};

?>
