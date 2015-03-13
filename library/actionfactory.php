<?php
require_once('action_base.php');
class actionFactory{
	static function get($controller, $action = '', $params = array()){
		static $actions = array();
		if(empty($controller)){
			$controller = Zend_Controller_Front::getInstance();
		}
		else{
			$params = $controller->getRequest()->getParams();
		}
// print_r($params);		
		if(!empty($action))
			$params['oper'] = $action;
		$action = 'action_'.(isset($params['oper']) ? $params['oper'] : $params['action']);
			
		if ($action == 'action_edit')$action = 'action_save';
		$db = $params['db'];
		$table = $params['table'];
		$index = $db.'_'.$table.'_'.$action;
		if (!isset($actions[$index])){
			$found = false;
			$dirs = array(
				"jqgrid/$db/$table"=>$db.'_'.$table.'_'.$action,
				"jqgrid/$db"=>$db.'_'.$action, 
				"jqgrid"=>$action);
			foreach($dirs as $dir=>$className){
				$classFile = realpath(APPLICATION_PATH."/$dir/$action.php");
				if(file_exists($classFile)){
					$found = true;
					break;
				}
			}
			if(!$found){
				$classFile = 'action_base.php';
				$className = 'action_base';
			}
			require_once($classFile);
			$actions[$index] = new $className($controller); 
		}
		$actions[$index]->setParams($params);
		return $actions[$index];
	}
}

?>