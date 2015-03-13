<?php
require_once('exporter_excel.php');
/*
应包含一些Sheets：
1. project-compiler-module情况
2. module-project-compiler情况
3. detail：testcase-project-compile情况
4. 各个project的情况

测试结果只显示Total、Pass、Fail and Others
如果选择了多个Release，则每个Release的情况并列
*/
class xt_zzvw_prj_exporter_srs extends exporter_excel{
	protected $data = array();
	protected $db = null;
	protected function init($params = array()){
		parent::init($params);
		$this->params['id'] = explode(',', $this->params['id']);
//print_r($this->params);		
//Array ( [db] => xt [table] => zzvw_prj [export_type] => last_result [rel_ids] => Array ( [0] => 1 [1] => 2 ) [id] => 10,9 [real_table] => prj ) 
		$this->db = dbFactory::get($this->params['db']);
	}
	
	public function setOptions($jqgrid_action){
		$this->params['sheets'] = array(
			0=>$this->getSRSSheet($jqgrid_action),
			1=>$this->getSRSHistorySheet($jqgrid_action)
		);
	}
	
	protected function getSRSSheet($jqgrid_action){
		$sheet = array('title'=>'Sys Requirements', 'startRow'=>2, 'startCol'=>1);
		$row1 = $row0 = array('srs_module', 'code');
		foreach($this->params['id'] as $prj_id){
			$row0[] = 'prj.'.$prj_id.'.4';
			foreach(array('ver', 'content', 'update_comment', 'cases') as $r){
				$row1[] = $r.'.'.$prj_id.'.prj';
			}
		}
		$row0[] = 'changed';
		$row1[] = 'changed';
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		foreach($row1 as $field){
			$sheet['header']['rows'][1][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['header']['mergeCols'] = array(
			'srs_module'=>array(2, 3), 'code'=>array(2, 3), 'changed'=>array(2, 3)
		);
		$sheet['groups'] = array(array('index'=>'srs_module'));
		$sheet['data'] = $this->getData('srs');
		return $sheet;
	}
	
	protected function getSRSHistorySheet($jqgrid_action){
		$sheet = array('title'=>'Edit History', 'startRow'=>2, 'startCol'=>1);
		$row0 = array('updated', 'code', 'content');
		foreach($this->params['id'] as $prj_id){
			$row0[] = 'act.'.$prj_id;
		}
		foreach($row0 as $field){
			$sheet['header']['rows'][0][] = $this->getFieldHead($field, $jqgrid_action);
		}
		$sheet['data'] = $this->getData('history');
		return $sheet;
	}
	
	protected function getFieldHead($field, $jqgrid_action){
		static $heads = array();
		if (empty($heads[$field])){
			$fields = explode('.', $field);
			$index = implode('_', $fields);
			$count = count($fields);
			switch($count){
				case 1:
					$real_field = $fields[0];
					$label = $fields[0];
					break;
				case 2:// rel.01
					$real_field = $fields[0];
					$label = $fields[0];
					$id = $fields[1];
					break;
				case 3: // rel.01.pass
					$real_field = $fields[0];
					$label = $fields[0];
					$id = $fields[1];
					break;
			}
			$head = array('label'=>ucfirst($label), 'index'=>$index, 'width'=>100);
			switch($real_field){
				case 'prj':
					$res = $this->db->query("SELECT * FROM prj WHERE id=$id");
					$row = $res->fetch();
					$head['label'] = $row['name'];
					$head['width'] = 150;
					if ($count == 3)
						$head['cols'] = $fields[2];
					break;
				case 'act':
					$res = $this->db->query("SELECT * FROM prj WHERE id=$id");
					$row = $res->fetch();
					$head['label'] = $row['name'];
					$head['width'] = 150;
					break;
				case 'srs_module':
					$head['label'] = 'Component';
					break;
				case 'code':
					$head['label'] = 'Identifier';
					break;
				case 'content':
					$head['label'] = 'Requirements Text / Data';
					$head['width'] = 300;
					break;
				case 'update_comment':
				case 'ver':
				case 'cases':
				case 'changed':
					$head['hidden'] = true;
					break;
			}
			$heads[$field] = $head;
		}
		return $heads[$field];
	}
	
	protected function getData($sheet){
		if (empty($this->data)){
			$data = array();
			// 先获取具体的数据
			$condition = array(
				array('field'=>'prj_id', 'op'=>'in', 'value'=>$this->params['id']),
				array('field'=>'edit_status_id', 'op'=>'=', 'value'=>EDIT_STATUS_PUBLISHED)
			);
			$last_result = tableDescFactory::get('xt', 'prj_srs_node_ver');
			$components = $last_result->calcSqlComponents(array('db'=>'xt', 'table'=>'prj_srs_node_ver', 'searchConditions'=>$condition), false);
			$sql = $last_result->getSql($components);
			$res = $this->db->query($sql);
			while($row = $res->fetch()){
				$row = $last_result->getMoreInfoForRow($row);
				foreach(array('ver', 'content', 'update_comment') as $r){
					if (!empty($row[$r]))
						$data['srs'][$row['srs_module']][$row['code']][$r.'_'.$row['prj_id'].'_prj'] = $row[$r];
				}
				$data['srs'][$row['srs_module']][$row['code']]['cases_'.$row['prj_id'].'_prj'] = implode(',', $row['testcase_cover']);
				$data['history'][$row['srs_module']][$row['code']] = $row['history'];
			}
//print_r($data);			
			$this->handleSRSData($data['srs']);
			$this->handleHistoryData($data['history']);
		}
		return $this->data[$sheet];
	}
	
	protected function handleSRSData($data){
		$this->data['srs'] = array();
//print_r($data);		
		$i = 0;
		foreach($data as $module=>$module_data){
			foreach($module_data as $code=>$code_data){
				if (empty($code) || empty($code_data))
					continue;
				$this->data['srs'][$i] = array_merge($code_data, array('srs_module'=>$module, 'code'=>$code));
				$lastVer = -1;
				$changed = false;
				foreach($this->params['id'] as $prj_id){
					$index = 'ver_'.$prj_id.'_prj';
					if (empty($this->data['srs'][$i][$index]))
						continue;
					if ($lastVer == -1)
						$lastVer = $this->data['srs'][$i][$index];
					else if ($lastVer != $this->data['srs'][$i][$index]){
						$changed = true;
						break;
					}
				}
				$this->data['srs'][$i]['changed'] = $changed;
				$i ++;
			}
		}
	}
	
	protected function handleHistoryData($data){
		$this->data['history'] = array();
		$i = 0;
		$i = 0;
		foreach($data as $module=>$module_data){
			foreach($module_data as $code=>$code_data){
				if (!empty($code_data)){
					$this->data['history'][$i] = $code_data;
					if (!empty($code_data['act']))
						$this->data['history'][$i]['act_'.$code_data['prj_id']] = $code_data['act'];
					$i ++;
				}
			}
		}
	}
	
	protected function calcStyle($sheetIndex, $headerIndex, $content, $default = ''){
		$style = parent::calcStyle($sheetIndex, $headerIndex, $content, $default);
		if ($headerIndex == 'changed' && $content[$headerIndex]){
			$style = 'warning';
		}
		return $style;
	}
};

?>
 