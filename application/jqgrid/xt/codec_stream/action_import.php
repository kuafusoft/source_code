<?php
require_once(APPLICATION_PATH.'/jqgrid/action_import.php');

class xt_codec_stream_action_import extends action_import{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file_dir'] = '/jqgrid/xt/codec_stream';
		$config_types = array(
			'0'=>'',
			'stream.steps_format_tools.config.php'=>'Tool Steps by Xiaoyang',
			'stream.rules.shan.config.php'=>'Linux Rules For CS By Shan',
			'stream.amy.config.php'=>'Update MetaData Info by Amy',
			'stream.linux.shan.config.php'=>'Classfy and Tag For CS By Shan',
			'stream.type.shan.config.php'=>'Classify Modules by Shan',
			'stream.rules.faye.config.php'=>'Android Rules For CS By Faye',
			'stream.type.shan.config.php'=>'Classify Modules by Shan',
			'stream.config.php'=>'CS Config By Other',
			'stream_codec_with.config.php'=>'Update CS Android By XT Excel',
			'stream_type_with.config.php'=>'Update Type For CS By XT Excel',
			'stream_tag_with.config.php'=>'Add Tag For CS By XT Excel',
			'stream.rules.config.php'=>'Add Rules For CS By Codec',
		);
		$view_params['config_file'] = $config_types;
		return $view_params;
	}
	
	protected function handlePost(){
// print_r($this->params);
		if (!empty($this->params['config_file']))
			$this->params['config_file'] = APPLICATION_PATH.'/jqgrid/xt/codec_stream/'.$this->params['config_file'];
		switch(basename($this->params['config_file'])){
			case 'stream.config.php':
				
				// $this->params['sheetsNeedParse'] = array(
					// 'AACLCDEC', 
					// 'aacplus', 'mp3', 'wav', 'flac', 
					// 'ogg', 'wmastd', 'wmapro', 'wmalsl', 'ac3',
					// 'ddp', 'xvid', 'vc1', 'mpeg4', 'mpeg2', 
					// 'mjpeg', 'h264', 'h263', 'flv', 
					// 'f4v', 
					// 'webm', 
					// 'Customer streams'
				// );
		// print_r($_FILES);
				$class = 'codec_stream';
				break;
			case 'stream_prjtag_with.config.php':
// print_r('stream_prjtag_with.config.php');
				$class = 'prjtag';
				break;
			case 'stream.linux.shan.config.php':
				$class = 'codec_stream_linux';
				break;
			case 'stream.rules.config.php':
				$class = 'trickmode_type_rules';
				break;
			case 'stream.type.shan.config.php':
				$class = 'module_types';
				break;
			case 'stream.rules.faye.config.php':
			case 'stream.rules.shan.config.php':
				$class = 'stream_rules';
				break;
			case 'stream.steps_format_tools.config.php':
				$class = 'steps_tools';
				break;
			default:
				// case 'stream_type_with.config.php':
				// case 'stream_codec_with.config.php':
				// case 'stream_tag_with.config.php':
				$class = 'codec_stream_byxt';
				break;
		}
		if(!empty($class)){
			$importer = importerFactory::get($class, $this->params);
			$importer->setOptions($this);
			return $importer->import();
		}
		print_r("This Feature Does NOT Finish");
	}
	
	
}

?>