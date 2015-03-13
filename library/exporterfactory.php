<?php
require_once('exporter_base.php');
require_once('exporter_excel.php');
class exporterFactory{
	static function get($name, $params){
		static $exporters = array();
		$name = 'exporter_'.$name;
		$db = $params['db'];
		$table = $params['table'];
		$classFile = realpath(APPLICATION_PATH."/jqgrid/".$db."/".$table.'/'.$name.'.php');
		$className = $db.'_'.$table.'_'.$name;
		if (!isset($exporters[$className])){
			if (!file_exists($classFile)){
				$classFile = realpath(APPLICATION_PATH."/jqgrid/$name.php");
				$className = $name;
			}
// print_r("className = $className, file = $classFile\n");			
			if (!file_exists($classFile)){
				if ($name == 'exporter_excel')
					$exporters[$className] = new exporter_excel($params);
				else
					$exporters[$className] = new exporter_base($params);
			}
			else{
				require_once($classFile);
				$exporters[$className] = new $className($params);
			}
		}
		return $exporters[$className];
	}
}
?>