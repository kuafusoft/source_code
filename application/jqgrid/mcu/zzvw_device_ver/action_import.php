<?php
require_once(APPLICATION_PATH.'/jqgrid/action_import.php');
require_once(APPLICATION_PATH.'/jqgrid/mcu/zzvw_device_ver/parse_svd.php');

class mcu_zzvw_device_ver_action_import extends action_import{
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file'] = "import_type.phtml";
		$view_params['view_file_dir'] = '/jqgrid/mcu/zzvw_device_ver';
		return $view_params;
	}
	
	protected function handlePost(){
// print_r($this->params);	
// print_r($_FILES);	
		$jsCode = "<script>".
			"parent.endImport('{$this->params['db']}', '{$this->params['table']}');".
			"</script>";
		print_r($jsCode);
		if($_FILES["uploaded_file"]["error"] != UPLOAD_ERR_OK) 
			return "UPLOAD ERROR";
			
		//检查后缀
		$uploads_dir = APPLICATION_PATH.'/upload/svd';
		$tmp_name = $_FILES["uploaded_file"]["tmp_name"];
		$name = $_FILES["uploaded_file"]["name"];
		$info = pathinfo($name);
		if($info['extension'] != 'svd'){
			return "WARNING: Only .svd file supported";
		}
		move_uploaded_file($tmp_name, "$uploads_dir/$name");
	
		$uploaded_file = "$uploads_dir/$name";
		$svd_parse = new svd_parse($uploaded_file);
		$ret = $svd_parse->parse();
		if($ret == ERROR_OK)
			return "Success";
		return "FAIL";
	}
}

?>