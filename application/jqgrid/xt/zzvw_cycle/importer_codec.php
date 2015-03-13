<?php
/*
从一个Codec的Excel文件中导入数据，包括Case数据、Stream数据、Trick数据和测试结果数据
*/

require_once('importer_testcase.php');

class xt_zzvw_cycle_importer_codec extends importer_testcase{
	public function setOptions($jqgrid_action){
		$this->testcase_type = 'CODEC';
	}
	
	protected function _import($fileName){
		$this->parse($fileName);
		// return $this->process();
	}
	
	protected function analyze_codec_result($sheet, $title){
		$highestRow = $sheet->getHighestRow(); // e.g. 10
		$highestColumn = $sheet->getHighestColumn(); // e.g 'F'	
		$cm = $this->getColumnMap($title, $highestColumn);
		if (!empty($cm)){
			for($row = $cm['start_row']; $row <= $highestRow; $row ++){
				foreach($cm['stream'] as $key=>$col){
					$this->parse_result[$title][$row][$key] = $this->getCell($sheet, $row, $col);
				}
			}
		}
	}

	
	protected function processSheetData($title, $sheet_data){
		$config_file = basename($this->params['config_file']);
		foreach($sheet_data as $case){
			if(!empty($case['name'])){
				// $name = basename($case['name']);
				// $location = dirname($case['name']);
				// $case['name'] = $name;
				// if(!empty($location) && $location != "."){
					// $case['location'] = str_ireplace("/", "\\", substr($location, 8))."\\";
				$res = $this->tool->query("select id from codec_stream where code = ".$case['code']);
				if($row = $res->fetch()){
					$sql0 = "select id, testcase_id from cycle_detail dt".
						" left join testcase tc on tc.id = tc.testcase_id".
						" where dt.codec_stream_id in (".$row['id'].") and tc.testcase_type_id = 2";
					$res0 = $this->tool->query($sql);
					while($row0 = $res0->fetch()){
						
					}
				}
// print_r('location:'.$case['location']."\n");
			}
// print_r('name:'.$case['name']."\n");
			if($config_file == 'jellybean_codec_with.config.php'){
				$codec_stream_id = $this->processStream($case['stream']);
				// foreach($case['trickmode'] as $code){
					// $caseInfo['code'] = $code;
					// $case_info = $this->processCase($caseInfo);
				// }
				//result process
			}
			else if ($config_file == 'jellybean_codec_without.config.php'){
				$case_info = $this->processCase($case);	
				//result process
			}
		}
	}
	
	protected function processCase($case){
print_r('cccccccccccccccccccccccccccccccccccccccc');
		// $transfer_fields = array('testcase_type'=>$this->testcase_type, 'testcase_module', 'testcase_testpoint', 'testcase_category'=>'Function', 'testcase_source'=>'FSL MAD', 'auto_level'=>'MANUAL', 'testcase_priority'=>'P3');
		// $fields_value = $this->tool->extractItems($transfer_fields, $case);
		// foreach($fields_value as $field=>$value){
			// if (empty($case[$field.'_id'])){
				// $case[$field.'_id'] = $this->getElementId($field, array('name'=>$value), array('name'));
				// unset($case[$field]);
			// }
		// }
		// $case_fields = array('testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 'code', 'summary'=>'', 'testcase_category_id', 'testcase_source_id');
		// $case_value = $this->tool->extractItems($case_fields, $case);
		// $newCase = false;
		// $case_id = $this->getElementId('testcase', $case_value, array('code'), $newCase);
		
		// $ver_fields = array('ver'=>1, 'auto_level_id'=>AUTO_LEVEL_MANUAL, 'testcase_priority_id'=>3, 'auto_run_minutes'=>0, 'manual_run_minutes'=>0, 'command'=>' ', 
			// 'objective'=>' ', 'precondition'=>' ', 'steps'=>' ', 'expected_result'=>' ', 'resource_link'=>' ', 'parse_rule_id'=>1, 'parse_rule_content'=>' ');
		// $ver_values = $this->tool->extractItems($ver_fields, $case);
		// $ver_values['testcase_id'] = $case_id;
		// $ver_values['edit_status_id'] = EDIT_STATUS_PUBLISHED;
		// $newVer = false;
		// $version_id = $this->getElementId('testcase_ver', $version, array(), $newVer);
		// if ($newVer && !$newCase){
			// $res = $this->tool->query("SELECT max(ver) as max_ver FROM testcase_ver WHERE testcase_id=$case_id");
			// $row = $res->fetch();
			// $max_ver = $row['max_ver'];
			// $this->tool->update('testcase_ver', array('ver'=>$max_ver + 1, 'created'=>date('Y-m-d H:i:s')), "id=$version_id");
		// }
		// if ($newVer){
			// $res = $this->tool->query("SELECT id as testcase_ver_id, testcase_id, owner_id, testcase_priority_id, edit_status_id, auto_level_id FROM testcase_ver WHERE id=$version_id");
			// $ver = $res->fetch();
			
			// $prj_ids = isset($case['prj_ids']) ? $case['prj_ids'] : $this->params['prj_ids'];
			// foreach($prj_ids as $prj_id){
				// $link = $ver;
				// $link['prj_id'] = $prj_id;
				// $history = array('prj_id'=>$prj_id, 'testcase_id'=>$ver['testcase_id'], 'act'=>'remove');
				// $res = $this->tool->query("SELECT * FROM prj_testcase_ver WHERE prj_id=$prj_id AND testcase_id={$ver['testcase_id']} AND edit_status_id IN (".EDIT_STATUS_PUBLISHED.','.EDIT_STATUS_GOLDEN.")");
				// while($row = $res->fetch()){
					// $this->tool->delete('prj_testcase_ver', "prj_id=$prj_id AND testcase_id={$ver['testcase_id']}");
					// $history['testcase_ver_id'] = $row['testcase_ver_id'];
					// $this->tool->insert('prj_testcase_ver_history', $history);
				// }
				// $history['testcase_ver_id'] = $ver['testcase_ver_id'];
				// $history['act'] = 'add';
				// $this->tool->insert('prj_testcase_ver', $link);
				// $this->tool->insert('prj_testcase_ver', $history);
			// }
		// }
		return array('testcase_id'=>$caseid, 'testcase_ver_id'=>$version_id);
	}
	
	private function processStream($stream){
		static $streamInfo = array();
		static $releventInfo = array();		
		if(isset($streamInfo[$stream['name']]))
			return $streamInfo[$stream['name']];
		else{
			$transfer_fields = array('stream_demuxer_format', 'stream_video_profile', 'stream_display_aspect_ratio', 'stream_video_bit_depth', 'stream_audio_profile',
				'stream_audio_bitrate_mode', 'stream_audio_bit_depth', 'codec_stream_type', 'codec_stream_format', 'codec_stream_v4cc', 
				'codec_stream_container', 'codec_stream_a_codec', 'testcase_priority');
			foreach($transfer_fields as $f){
				if (!empty($stream[$f])){
					if(isset($releventInfo[$f][$stream[$f]]))
						$stream[$f.'_id'] = $releventInfo[$f][$stream[$f]];
					else{
						$releventInfo[$f][$stream[$f]] = $this->getElementId($f, array('name'=>$stream[$f]));
						$stream[$f.'_id'] = $releventInfo[$f][$stream[$f]];
					}
				}
// print_r($releventInfo);
				unset($stream[$f]);
			}
			// handle resolution
			if (!empty($stream['resolution']) && preg_match('/^(\d+)x(\d+)$/', $stream['resolution'], $matches)){
				$stream['v_width'] = $matches[1];
				$stream['v_height'] = $matches[2];
			}
			if (!empty($stream['a_duration']) ){
				if(preg_match('/(.+)(mn{1})(.+)(s{1})$/', $stream['a_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "0".trim($matches[1]);
					if(strlen(trim($matches[3])) == 1)
						$matches[3] = "0".trim($matches[3]);
					$stream['a_duration'] = "00:".trim($matches[1]).":".trim($matches[3]).".000";
				}
				else if(preg_match('/(.+)(s{1})(.+)(ms{1})$/', $stream['a_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "0".trim($matches[1]);
					if(strlen(trim($matches[3])) == 1)
						$matches[3] = "00".trim($matches[3]);
					else if(strlen(trim($matches[3])) == 2)
						$matches[3] = "0".trim($matches[3]);
					$stream['a_duration'] = "00:00:".trim($matches[1]).".".trim($matches[3]);
				}
				else if(preg_match('/(.+)(ms{1})$/', $stream['a_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "00".trim($matches[1]);
					else if(strlen(trim($matches[1])) == 2)
						$matches[1] = "0".trim($matches[1]);
					$stream['a_duration'] = "00:00:00.".trim($matches[1]);
				}
			}
			
			if (!empty($stream['v_duration']) ){
				if(preg_match('/(.+)(mn{1})(.+)(s{1})$/', $stream['v_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "0".trim($matches[1]);
					if(strlen(trim($matches[3])) == 1)
						$matches[3] = "0".trim($matches[3]);
					$stream['v_duration'] = "00:".trim($matches[1]).":".trim($matches[3]).".000";
				}
				else if(preg_match('/(.+)(s{1})(.+)(ms{1})$/', $stream['v_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "0".trim($matches[1]);
					if(strlen(trim($matches[3])) == 1)
						$matches[3] = "00".trim($matches[3]);
					else if(strlen(trim($matches[3])) == 2)
						$matches[3] = "0".trim($matches[3]);
					$stream['v_duration'] = "00:00:".trim($matches[1]).".".trim($matches[3]);
				}
				else if(preg_match('/(.+)(ms{1})$/', $stream['v_duration'], $matches)){
					if(strlen(trim($matches[1])) == 1)
						$matches[1] = "00".trim($matches[1]);
					else if(strlen(trim($matches[1])) == 2)
						$matches[1] = "0".trim($matches[1]);
					$stream['v_duration'] = "00:00:00.".trim($matches[1]);
				}
			}
// print_r($stream);			
			$stream_id = $this->getStreamID($stream);
			$streamInfo[$stream['name']] = $stream_id;
			return $streamInfo[$stream['name']];
		}
	}
	
	private function getStreamID($valuePair){
		$is_new = true;
		$id = '';
		$res = $this->tool->query("describe codec_stream");
		static $i = 0;
		while($row = $res->fetch()){
			if (isset($valuePair[$row['Field']]))
				$realVP[$row['Field']] = $valuePair[$row['Field']];
		}
		$sql = "SELECT * FROM `codec_stream` where `name`='".$realVP['name'];
		if(!empty($realVP['location']))
			$sql .= "' and location = '".mysql_real_escape_string($realVP['location'])."'";
		$res = $this->tool->query($sql);
		if ($row = $res->fetch()){
			// $this->tool->update('codec_stream', $realVP, "id=".$row['id']);
print_r($row['id']."update"."\n");
			$is_new = false;
			return $row['id'];
		}

		$is_new = true;
print_r("new"."\n");
		// $this->tool->insert('codec_stream', $realVP);
		$element_id = $this->tool->lastInsertId();
		return $element_id;
	}
};


?>