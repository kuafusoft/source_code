<?php
require_once('kf_cell.php');

class kf_file extends kf_cell{
	protected function init($params, $values){
		parent::init($params, $values);
	}

	protected function oneEdit($value, $props){
		$this->oneView($value, $props);
	}
	
	protected function oneView($value, $props){
		unset($props['type']);
		unset($props['value']);
// print_r($value);	
		//某些类型的文件允许浏览
		$pathinfo = pathinfo($value);
		switch($pathinfo['extension']){
			case 'txt':
			case 'log':
			case 'html':
			case 'php':
			case 'ini':
			case 'c':
			case 'cpp':
			case 'h':
			case 'hpp':
			case 'bat':
			case 'pl':
			case 'py':
			case 'java':
			case 'xml':
				$props['event']['onmouseover'] = "XT.getFileContent(this,\"".urlencode($value)."\")"; 	//将内容显示在title里
				break;
			case 'jpg':
			case 'png':
			case 'gif':
			case 'img':
				//计算相对地址
				if(!empty($this->params['rel'])){
					$rel = $this->params['rel'];
					
					$props['event']['onmouseover'] = "XT.previewPicture(event, this,\"$rel\")"; 	//将内容显示在title里
					$props['event']['onmouseout'] = "XT.clearPicture(this)";
				}
				break;
		
		}
		$props['title'] = 'click to download it';
		$strProps = $this->propStr($props);
		$str = "<a href='/download.php?filename=".urlencode($value)."&remove=1' $strProps>{$pathinfo['basename']}</a>";
		return $str;
	}
	
}
?>