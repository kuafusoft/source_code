<?php
require_once('table_desc.php');

class xt_zzvw_cycle_detail_for_report2 extends table_desc{
	protected function init($db, $table, $params = array()){
        parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink';	
		$this->options['real_table'] = 'cycle_detail';
        $this->options['list'] = array(
			// '*'
			'id',
			'prj',
			'cycle_id',
			'module',
			'code'=>array('label'=>'Testcase'),
			'summary',
			'test_env_id',  
			'build_result_id'=>array('formatter'=>'select', 'data_source_table'=>'result_type'), 
			'result_type_id', 
			'finish_time', 
			'duration_minutes', 
			'deadline', 
			'tester_id', 
			'defect_ids', 
			'comment', 
			'issue_comment',
		);
    }

	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		$components['main']['fields'] = "`id`, `cycle_id`, IFNULL(codec_stream_format, module) module, IFNULL(codec_stream, `code`) as code, ".
			"IFNULL(codec_stream_name, summary) summary, `test_env_id`,  `build_result_id`, `result_type_id`, `finish_time`, `duration_minutes`,".
			"`deadline`, `tester_id`, `defect_ids`, `comment`, `task_detail_id`, `issue_comment`";
		
		if(empty($components['order']))
			$components['order'] = 'id desc';
		return $components;
	}
}

?>