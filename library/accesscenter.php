<?php
require_once('dbfactory.php');
defined('CAN_ACCESS') || define('CAN_ACCESS', 1);
defined('NO_ENOUGH_RIGHT') || define('NO_ENOUGH_RIGHT', 0);

class accessCenter{
    static $access_matrix = array();
	 
	static function setAccessMatrix($am){
		self::$access_matrix = array_merge(self::$access_matrix, $am);
// print_r(self::$access_matrix)		;
	}
	
	/*
	策略：没有定义的视为允许，只有明确规定不能访问的视为不允许。一个人拥有多个Role，只有所有拥有的Role都不允许的时候才视为不允许
	*/
	static function canAccess($controllerName, $action, $params, $roles){
		$allowed = 0;
		if($controllerName == 'jqgrid'){
			$db = $params['db'];
			$table = $params['table'];
			$controllerName = $db.'_'.$table;
		}
// print_r($roles);
// print_r($action);		
// print_r($controllerName);	
// print_r(self::$access_matrix);
		if(!isset(self::$access_matrix[$controllerName]['all']['all']))
			self::$access_matrix[$controllerName]['all']['all'] = true;
		foreach($roles as $role){
			if(!isset(self::$access_matrix[$controllerName][$role]['all'])) //从all-all继承
				self::$access_matrix[$controllerName][$role]['all'] = (int)self::$access_matrix[$controllerName]['all']['all'];
			if(!isset(self::$access_matrix[$controllerName]['all'][$action])) //从all-all继承
				self::$access_matrix[$controllerName]['all'][$action] = (int)self::$access_matrix[$controllerName]['all']['all'];
			if(!isset(self::$access_matrix[$controllerName][$role][$action])){ //从最近的继承，认为role-all和all-action并列
				self::$access_matrix[$controllerName][$role][$action] = (int)self::$access_matrix[$controllerName][$role]['all'] + (int)self::$access_matrix[$controllerName]['all'][$action];
			}
			$allowed += (int)self::$access_matrix[$controllerName][$role][$action];
		}
		return $allowed;
	}
}

?>
