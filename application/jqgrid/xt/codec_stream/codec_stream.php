<?php
require_once('table_desc.php');

class xt_codec_stream extends table_desc{
    public function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$cart_data = new stdClass;
		$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"testcase_module_id","op":"in","data":"418,460"}]}';
		$this->options['linktype'] = 'infoLink_ver';
        $this->options['list'] = array(
			'id'=>array('editable'=>false, 'hidden'=>true, 'formatter'=>'infoLink'),
			'code'=>array('label'=>'S-ID'),
			'codec_stream_format_id'=>array('label'=>'Format'),
			'name'=>array('invalidChar'=>''),
			'codec_stream_type_id'=>array('label'=>'Type'),
			'testcase_priority_id'=>array('label'=>'Priority'),
			'location', 
			'codec_stream_container_id'=>array('label'=>'Container'),
			'codec_stream_v4cc_id'=>array('label'=>'V4CC'), 
			'codec_stream_a_codec_id'=>array('label'=>'Audio Codec'),
			'v_width'=>array('label'=>'Video Width', 'hidden'=>true),
			'v_height'=>array('label'=>'Video Height', 'hidden'=>true), 
			'v_framerate'=>array('label'=>'Video Framerate', 'hidden'=>true),
			'stream_video_profile_id'=>array('label'=>'Video Profile', 'hidden'=>true),
			'v_bitrate'=>array('label'=>'Video BitRate', 'hidden'=>true),
			'stream_video_bitrate_mode_id'=>array('label'=>'Video BitRate Mode', 'hidden'=>true),
			'stream_video_bit_depth_id'=>array('label'=>'Video Bit Depth', 'hidden'=>true),
			'v_track'=>array('label'=>'Video Track', 'hidden'=>true), 
			'a_samplerate'=>array('label'=>'Audio Samplerate', 'hidden'=>true),
			'stream_audio_profile_id'=>array('label'=>'Audio Profile', 'hidden'=>true),
			'a_channel'=>array('label'=>'Audio Channel', 'hidden'=>true),
			'stream_channel_mode_id'=>array('label'=>'Audio Channel Mode', 'hidden'=>true),
			'a_bitrate'=>array('label'=>'Audio BitRate', 'hidden'=>true),
			'stream_audio_bitrate_mode_id'=>array('label'=>'Audio BitRate Mode', 'hidden'=>true),
			'stream_audio_bit_depth_id'=>array('label'=>'Audio Bit Depth', 'hidden'=>true),
			'a_track'=>array('label'=>'Audio Track', 'hidden'=>true),
			'stream_display_aspect_ratio_id'=>array('label'=>'Display Aspect Ratio', 'hidden'=>true),
			'stream_demuxer_format_id'=>array('label'=>'Demuxer Format', 'hidden'=>true),
			'stream_color_space_id'=>array('label'=>'Color Space', 'hidden'=>true),
			'stream_scan_type_id'=>array('label'=>'Scan Type', 'hidden'=>true),
			'stream_endianness_id'=>array('label'=>'Endianness', 'hidden'=>true),
			// '*'=>array('hidden'=>true),
			// 'note'=>array('label'=>'Comment', 'hidden'=>true),
			// 'steps'=>array('editrules'=>array('required'=>false), 'editable'=>false, 'hidden'=>true),
			// 'precondition'=>array('editrules'=>array('required'=>false), 'editable'=>false, 'hidden'=>true),
			// 'command'=>array('editrules'=>array('required'=>false), 'excluded'=>true, 'rows'=>3, 'editable'=>false, 'hidden'=>true),
			// 'testcase_ids'=>array('label'=>'Default Actions', 'editrules'=>array('required'=>false), 'hidden'=>true, 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'testcase', 'cart_data'=>json_encode($cart_data)),
			'isactive'=>array('hidden'=>true),
			'ver_ids'=>array('label'=>'Versions', 'hidden'=>true, 'hidedlg'=>true, 'formatter'=>'text', 'editable'=>false),
			//'wheres'=>array('hidden'=>true, 'hidedlg'=>true, 'editrules'=>array('required'=>false), 'view'=>false)
		);
		$this->options['query'] = array(
			'normal'=>array('key'=>array('label'=>'Keyword'), 'codec_stream_format_id', 'isactive', 'testcase_priority_id', 'codec_stream_type_id'=>array('colspan'=>3)), 
			'buttons'=>array(
				'import'=>array('label'=>'Upload', 'title'=>'Import Stream'),
			), 
			'cols'=>3
		);
		$this->options['edit'] = $this->options['list'];
		unset($this->options['edit']['id']);
		unset($this->options['edit']['ver_ids']);
		
		if(isset($this->params['container'])){
			if($this->params['container'] == 'div_case_add_for_codec'){
				unset($this->options['query']['buttons']);
			}
		}
		$this->options['gridOptions']['label'] = 'Codec Stream';
		$this->options['navOptions']['refresh'] = false;
        $this->options['ver'] = '1.0';
		$this->linkTables = array(
			'ver'=>array(
				array('db'=>'xt', 'table'=>'codec_stream_ver', 'ver_field'=>'ver')
				)
			);
    } 
	
	protected function getQueryFields($params){	
		parent::getQueryFields($params);
		if(!empty($this->options['query']['normal']['testcase_priority_id'])){	
			$this->options['query']['normal']['testcase_priority_id']['edittype'] = 'checkbox';
			$this->options['query']['normal']['testcase_priority_id']['cols'] = '6';
			$this->options['query']['normal']['testcase_priority_id']['queryoptions']['value'] = '1,2,3';
		}	
		if(!empty($this->options['query']['normal']['codec_stream_type_id'])){	
			$this->options['query']['normal']['codec_stream_type_id']['edittype'] = 'checkbox';
			$this->options['query']['normal']['codec_stream_type_id']['cols'] = '9';
			$this->options['query']['normal']['codec_stream_type_id']['queryoptions']['value'] = '1,2,3,4,5,7,8';
		}	
		if(!empty($this->options['query']['normal']['isactive'])){	
			$this->options['query']['normal']['isactive']['queryoptions']['value'] = '1';
		}
		return $this->options['query'];
	}
	
	public function fillOptions(&$columnDef, $db, $table){
		$userTable = $this->userAdmin->getUserTable();
		if ("$db.$table" == $userTable){
			$userList= $this->userAdmin->getUserList(array('blank'=>true));
			$columnDef['editoptions']['value'] = $columnDef['formatoptions']['value'] = $userList;
			$activeUserList= $this->userAdmin->getUserList(array('blank'=>true, 'isactive'=>true));
			$columnDef['searchoptions']['value'] = $userList;
			$columnDef['addoptions']['value'] = $activeUserList;
		}
		else{
			$condition = null;
			if($table == 'codec_stream_format'){
// print_r($table);
				$field = "`".$table."`".".`isactive`";
				$op = '=';
				$value = '1';
				$condition = array(compact("field", "op", "value"));
			}
			$this->tool->fillOptions($db, $table, $columnDef, false, $condition);
		}
	}
	
	public function getMoreInfoForRow($row){
// print_r($this->params);
		if(!empty($this->params['parent'])){
			$res = $this->tool->query("select detail.test_env_id, cycle.prj_id from cycle_detail detail".
				" left join cycle on cycle.id = detail.cycle_id where detail.id=".$this->params['parent']);
			$detail = $res->fetch();
			$row['test_env_id'] = $detail['test_env_id'];
			$row['prj_id'] = $detail['prj_id'];
			$res = $this->tool->query("select env_item_ids from test_env where id=".$row['test_env_id']);
			if($info = $res->fetch()){
				$env_item = false;
				if(!empty($info['env_item_ids'])){
					$env_item = true;
					$res0 = $this->tool->query("select steps, precondition, command from stream_steps".
						" where codec_stream_type_id=".$row['codec_stream_type_id']." and env_item_id in (".$info['env_item_ids'].")");
					if($info0 = $res0->fetch()){
						$row['steps'] = $info0['steps'];
						$row['precondition'] = $info0['precondition'];
						$row['command'] = $info0['command'];
					}
					else
						$env_item = false;
				}
				if(!$env_item){
					$result = $this->tool->query("select os.name from prj left join os on prj.os_id = os.id where prj.id = ".$row['prj_id']);
					$os = $result->fetch();
// print_r($os);
					if(stripos(strtolower($os['name']), "android") !== false)
						$os_name = 'Android';
					else if(stripos(strtolower($os['name']), "linux") !== false)
						$os_name = 'Linux';
					$res1 = $this->tool->query("select steps.steps, steps.precondition, steps.command from stream_steps steps".
						" left join stream_tools tools on steps.env_item_id = tools.env_item_id".
						" where tools.codec_stream_format_id=".$row['codec_stream_format_id']." and tools.os='".$os_name."'");
					if($info1 = $res1->fetch()){
						$row['steps'] = $info1['steps'];
						$row['precondition'] = $info1['precondition'];
						$row['command'] = $info1['command'];
					}
				}
			}
		}
		return $row;
	}
}
