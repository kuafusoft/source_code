<?php
require_once('kf_cell.php');

class kf_dir extends kf_cell{
	protected function init($params, $values){
		parent::init($params, $values);
		$this->params['post'] = array('type'=>'button', 'value'=>'upload', 'event'=>array('onclick'=>"XT.upload(\"{$params['value']}\", \"{$params['name']}\")")); //如果是edit状态，则显示该按钮
		$this->params['dir'] = $params['value'];
		$this->params['rel'] = isset($params['rel']) ? $params['rel'] : '';
		$files = scandir($this->params['dir']);
		unset($this->params['value']);
		unset($files[0]); 	// .
		unset($files[1]);	// ..
		foreach($files as $file){
			if(is_dir($file))
				continue;
			$this->params['value'][$file] = $this->params['editoptions']['value'][$file] = $file; //将目录转化成文件集合
		}
		$this->multi_edit = true;
		$this->multi_value = true;
	}
	
	public function display($display_status = DISPLAY_STATUS_EDIT){
		return parent::display($display_status);
	}

	protected function oneEdit($value, $props){
		$filename = $this->params['dir'].'/'.$value;
		$rel_filename = $this->params['rel'].'/'.$value;
		return $this->showFile($filename, $rel_filename, $props);
	}
	
	protected function oneView($value, $props){
		return $this->oneEdit($value, $props);
	}
	
	protected function showFile($filename, $rel_filename, $props){
		$params = array('type'=>'file', 'name'=>'file_in_'.$this->params['name'].'[]', 'value'=>$filename, 'rel'=>$rel_filename);
		$file = cellFactory::get($params);
		return $file->display(DISPLAY_STATUS_VIEW);
	}
	
	protected function _getValue(){
		$value = isset($this->params['value']) ? $this->params['value'] : '';
		return $value;	
	}
	
}
?>