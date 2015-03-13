<?php
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
require_once('Zend/Db.php');
require_once('Zend/Controller/Front.php');
require_once(APPLICATION_PATH.'/models/Useradmin.php');

class dbFactory{
	static function get($db_name, & $realDbName = ''){
		static $dbs = array();
		static $db_map = array();
		if (!isset($dbs[$db_name])){
			$dsn = array();
			$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
			$multiDb = array();
			if($bootstrap){
				$multiDb = $bootstrap->getResource('multidb')->getOptions();
			}
			if (isset($multiDb[$db_name]))
				$dsn = $multiDb[$db_name];
			if (empty($dsn['dbname'])) $dsn['dbname'] = $db_name;
			if (!isset($dsn['host']))$dsn['host'] = 'localhost';
			if (!isset($dsn['username']))$dsn['username'] = 'root';
			if (!isset($dsn['password']))$dsn['password'] = 'dbadmin';
			if($db_name != 'useradmin'){
				$userAdmin = new Application_Model_Useradmin(null);
				$userInfo = $userAdmin->getUserInfo();
				$user_id = $userInfo->id;
				if($user_id > 0){
					$user_config = $userAdmin->getConfigInfo($db_name, $user_id);
					if(!empty($user_config)){
						$user_config = json_decode($user_config, true);
// print_r($user_config)						;
						$dsn = array_merge($dsn, $user_config);
						
					}
				}
			}
			$db_map[$db_name] = $dsn['dbname'];
			try{
				$db = Zend_Db::factory('PDO_MYSQL', $dsn);
				$db->setFetchMode(Zend_Db::FETCH_ASSOC);
				$dbs[$db_name] = $db;
			}catch(Exception $e){
				print_r($e->getMessage());
				$dbs[$db_name] = null;
			}
		}
		$realDbName = $db_map[$db_name];
		return $dbs[$db_name];
	}
}

?>