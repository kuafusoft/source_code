<?php
class toolFactory{
	static function get($params = array()){
// print_r($params);	
		static $tools = array();
		if (is_string($params)){
// print_r("params = $params\n");		
			$className = 'tool_'.$params;
		}
		else
			$className = 'tool_'.(isset($params['tool']) ? $params['tool'] : 'kf');
		$index = $className;
		if(!empty($params['db']) && !empty($params['table']))
			$index = $params['db'].'.'.$params['table'];
		if (empty($tools[$index])){
			switch($className){
				case 'tool_jqgrid':
				case 'tool_kf':
				case 'tool_db':
					$classDir = realpath(APPLICATION_PATH."/../library");
					break;
				default:
// print_r($params);				
					$classDir = realpath(APPLICATION_PATH."/jqgrid/".$params['db']."/".$params['table']);
					break;
			}
			$classFile = $classDir.'/'.$className.'.php';
// print_r("classFile = $classFile, className = $className\n");			
			if (file_exists($classFile)){
				require_once($classFile);
				$tools[$index] = new $className();
			}
			else{
				require_once('tool_kf.php');
				$tools[$index] = new tool_kf();
			}
		}
		return $tools[$index];
	}
}
?>