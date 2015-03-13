<?php
require_once('table_desc.php');
class tableDescFactory{
	static function get($db, $table, $params = array(), $action = null){
		static $desc = array();
// print_r($params);
		// static $count = 0;
		// $count ++;
		// if($count > 5)
			// exit(1);
		$className = $index = $db.'_'.$table;
// print_r("get $db.$table\n");
		if (empty($desc[$index])){
// print_r("new $db.$table\n");
			$classFile = realpath(APPLICATION_PATH."/jqgrid/$db/$table/$table.php");
			$params['self_action'] = $action;
// print_r("className = $className, classFile = $classFile");
			if (file_exists($classFile)){
				require_once($classFile);
				$desc[$index] = new $className($db, $table, $params); 
			}
			else
				$desc[$index] = new table_desc($db, $table, $params);
			// $desc[$index]->getOptions();
		}
		// $desc[$index]->setParams($params);
		return $desc[$index];
	}
}

?>