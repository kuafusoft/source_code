<?php
require_once('importer_base.php');
require_once('importer_excel.php');
class importerFactory{
	static function get($name, $params){
		static $importers = array();
		$name = 'importer_'.$name;
		$db = $params['db'];
		$table = $params['table'];
		$classFile = realpath(APPLICATION_PATH."/jqgrid/".$db."/".$table).'/'.$name.'.php';
		$className = $db.'_'.$table.'_'.$name;
		if (!isset($importers[$className])){
			if (!file_exists($classFile)){
				$classFile = realpath(APPLICATION_PATH."/jqgrid").'/'.$name.'.php';
				$className = $name;
			}
//print_r("className = $className, file = $classFile\n");			
			if (!file_exists($classFile)){
				if ($name == 'importer_excel')
					$importers[$className] = new importer_excel($params);
				else
					$importers[$className] = new importer_base($params);
			}
			else{
				require_once($classFile);
				$importers[$className] = new $className($params);
			}
		}
		return $importers[$className];
	}
}

?>