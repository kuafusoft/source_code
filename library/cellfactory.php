<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
class cellFactory{
	static function get($params, $values = array()){
		$type = isset($params['type']) ? $params['type'] : 'text';
		$classFile = APPLICATION_PATH.'/../library/cell/'.$type.'.php';
		$className = 'kf_'.$type;
		if (!file_exists($classFile)){
			$classFile = APPLICATION_PATH.'/../library/kf_cell.php';
			$className = 'kf_cell';
		}
		require_once($classFile);
		$cell = new $className($params, $values); 
		return $cell;
	}
}
?>