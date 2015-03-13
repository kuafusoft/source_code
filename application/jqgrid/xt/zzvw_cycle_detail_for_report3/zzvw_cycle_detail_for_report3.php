<?php
require_once('table_desc.php');

class xt_zzvw_cycle_detail_for_report3 extends table_desc{
	protected function init($db, $table, $params = array()){
        parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink';	
		$this->options['real_table'] = 'cycle_detail';
        $this->options['list'] = array(
			// '*'
			'id'=>array('hidden'=>true),
			'prj',
			'cycle',
			'module',
			'testcase'=>array('label'=>'Testcase'),
			'summary',
			'test_env_id'=>array('hidden'=>true),  
			'build_result_id'=>array('hidden'=>true, 'formatter'=>'select', 'data_source_table'=>'result_type'), 
			'result_type_id', 
			'finish_time'=>array('hidden'=>true), 
			'duration_minutes'=>array('hidden'=>true), 
			'tester_id'=>array('hidden'=>true), 
			'defect_ids', 
			'comment', 
			'issue_comment',
		);
    }

	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		$components['main']['fields'] = "`id`, prj, `cycle`, IFNULL(codec_stream_format, module) module, IFNULL(codec_stream, `testcase`) as testcase, ".
			"IFNULL(codec_stream_name, summary) summary, `test_env_id`,  `build_result_id`, `result_type_id`, `finish_time`, `duration_minutes`,".
			"`tester_id`, `defect_ids`, `comment`, `issue_comment`";
		
		if(empty($components['order']))
			$components['order'] = 'id desc';
		return $components;
	}
}

?>