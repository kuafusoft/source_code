<?php
require_once('table_desc.php');

class workflow_daily_note extends table_desc{
	protected function init($db, $table, $params = array()){
// $p = $params;
// unset($p['self_action']);
// print_r($p);	
		parent::init($db, $table, $params);
		$this->options['list'] = array(
			'id',
			'content',
			'daily_note_type_id'=>array('label'=>'Type', 'editrules'=>array('required'=>true)),
			'item_prop_id'=>array('label'=>'Property'),
			'*',
			'prj_id'=>array('label'=>'Project'),
			'progress'
		);
		$this->options['edit'] = array('prj_id', 'daily_note_type_id', 'content', 'item_prop_id');
		$this->options['query'] = array(
			'buttons'=>array(
				'new'=>array('label'=>'New', 'onclick'=>'XT.go("/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/testcase/element/0")', 'title'=>'Create New Testcase'),
			), 
			'normal'=>array('key'=>array('label'=>'Keyword'), 'daily_note_type_id', 'period_id', 
				'levels'=>array('DATA_TYPE'=>'int', 'edittype'=>'select', 'searchoptions'=>array('value'=>array(0=>' ', 'myself-level'=>'Myself Notes Only', 'son-level'=>'Son Level Only', 'sub-levels'=>'All Sub-levels'))), 
				'creater_id', 'item_prop_id')
		);
		
		$this->options['parent'] = array('table'=>'prj', 'field'=>'prj_id');
	}
	
	protected function getSpecialFilters(){
		$special = array('period_id', 'levels', 'prj_id');
		return $special;
	}
	
	protected function specialSql($special, &$ret){
//print_r($special);
		foreach($special as $k=>$v){
			switch($v['field']){
				case 'period_id':
					$res = $this->db->query("SELECT * FROM period WHERE id IN ({$v['value']})");
					if ($row = $res->fetch()){
						$ret['where'] .= " AND created>='{$row['from']}' AND created<='{$row['end']} 23:59:59'";
					}
					break;
				case 'levels':
					$creater_ids = implode(',', $this->userAdmin->getSubUsers($v['value'], true));
					$ret['where'] .= " AND creater_id in ($creater_ids)";
					break;
				case 'prj_id':
					$ret['where'] .= " AND prj_trace.prj_id=".$v['value'];
					$ret['main']['from'] .= " LEFT JOIN prj_trace ON daily_note.id=prj_trace.daily_note_id";
					break;
			}
		}
	}
	
	public function getMoreInfoForRow($row){
		switch($row['daily_note_type_id']){
			case 2: // prj_trace
			case 3: // summary report
				$res = $this->db->query("SELECT prj_id, progress FROM prj_trace WHERE daily_note_id=".$row['id']);
				$prj = $res->fetch();
				$row['prj_id'] = $prj['prj_id'];
				$row['progress'] = $prj['progress'];
				break;
		}
		return $row;
	}
}
?>