<?php
/*
从一个Codec的Excel文件中导入数据，包括Case数据、Stream数据、Trick数据和测试结果数据
*/

require_once('importer_testcase.php');

class importer_codec extends importer_testcase{
	public function setOptions($jqgrid_action){
		$this->testcase_type = 'CODEC';
	}
	
	protected function analyze_codec_case($sheet, $title){
		$columnMap = $this->getColumnMap($title);
		$stream_type = $columnMap['stream']['codec_stream_type'];
		$highestRow = $sheet->getHighestRow(); // e.g. 10
		$highestColumn = $sheet->getHighestColumn(); // e.g 'F'
		$this->handleTrickmode($sheet, $columnMap, $title, $stream_type);
		
		for($row = $columnMap['test_result']['start_row']; $row <= $highestRow; $row ++){
			$this->handleStream($sheet, $row, $columnMap['stream'], $title);
			$this->handleTestResult($sheet, $row, $columnMap, $title);
		}
	}
	
	public function analyze_video_basic_function($sheet, $title){
		$this->analyze_codec_case($sheet, $title);
	}

	public function analyze_audio_basic_function($sheet, $title){
		$this->analyze_codec_case($sheet, $title);
	}
	
	public function analyze_streaming_basic_function($sheet, $title){
		$this->analyze_codec_case($sheet, $title);
	}
	
	public function analyze_image_basic_function($sheet, $title){
		$this->analyze_codec_case($sheet, $title);
	}
	
	public function analyze_stress($sheet, $title){
		$this->analyze_codec_case($sheet, $title);
	}

	
	private function handleTestResult($sheet, $row, $columnMap, $title){
		$col = $columnMap['trickmode']['from'];
		$trickmode_end = $columnMap['trickmode']['end'];
		$test_envs = array();
		do{
			$result = trim(strtolower($this->getCell($sheet, $row, $col)));
			if (!empty($result)){
				$env = $this->getCell($sheet, $columnMap['row_for_test_env'], $col);
				if (!empty($env))
					$test_envs[$env] = $env;
				$comment = $this->getCell($sheet, $row, $columnMap['test_result']['comment']);
				//$comment = '';//implode('\n', $this->getComment($sheet, $row, $col));
				if (is_null($comment))
					$comment = '';
				
				$this->parse_result[$title]['test_result'][$row][$col] = array(
					'result'=>$result, 
					'comment'=>$comment
				);
				if (!empty($env))
					$this->parse_result[$title]['test_result'][$row][$col]['env'] = array($env=>$env);
				else
					$this->parse_result[$title]['test_result'][$row][$col]['env'] = $test_envs;
			}
			$col = $this->nextCol($col);
		}while($col != $trickmode_end);
	}
	
	/*
	将Trickmode看成是Case，而Stream则是该Case运行所需要的Resource
	*/
	private function handleTrickmode($sheet, $columnMap, $title, $stream_type){
		$from = $columnMap['trickmode']['from'];
		$trickmode_end = $columnMap['trickmode']['end'];
		$row_for_test_env = $columnMap['row_for_test_env'];
		$expected_result = $this->getCell($sheet, 3, $columnMap['trickmode']['expected_result']);
		if (empty($this->parse_result[$title]))
			$this->parse_result[$title] = array('format'=>'codec');
		do{
			$summary = $code = trim($this->getCell($sheet, $columnMap['trickmode']['row_for_name'], $from));
			$code = $stream_type.'-'.preg_replace('/[\s\/\\\]/', '-', $code);
			$objective = $this->getCell($sheet, $columnMap['trickmode']['row_for_objective'], $from);
			$this->parse_result[$title]['testcase'][$from] = array('code'=>$code, 'summary'=>$summary, 'testcase_type'=>$this->testcase_type, 
				'objective'=>$objective, 'expected_result'=>$expected_result, 'testcase_module'=>'Codec Trickmode', 
				'testcase_testpoint'=>$stream_type.' Codec Trickmode', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD');
			$from = $this->nextCol($from);
		}while($from != $trickmode_end);
	}

/*	
	'stream'=>array(
		'name'=>'C',
		'location'=>'B',
		'codec_stream_v4cc'=>'G',
		'codec_stream_container'=>'H',
		'resolution'=>'I',
		'v_framerate'=>'J',
		'v_other_info'=>'K',
		'codec_stream_a_codec'=>'L',
		'a_channel'=>'M',
		'a_samplerate'=>'N',
		'a_bitrate'=>'O',
		'a_mode'=>'P',
		'a_other_info'=>'Q',
		),
*/		
	private function handleStream($sheet, $row, $columnMap, $title){
		$stream = array();
		foreach($columnMap as $field=>$col){
			if ($field == 'codec_stream_type')
				$stream[$field] = $col;
			else
				$stream[$field] = $this->getCell($sheet, $row, $col);
		}
		$this->parse_result[$title]['stream'][$row] = $stream;
	}
};


?>