<?php

class Application_Model_DbTable_Guestbook extends Zend_Db_Table_Abstract
{
    protected $_name = 'guestbook';
/*
    protected function _setup()
    {
//        $config = new Zend_Config_Ini('/application/configs/application.ini', 'production');
        
        $params = array (
            'host'     => 'localhost',
            'username' => 'root',
            'password' => 'dbadmin',
            'dbname'   => 'test'
        );
        $db = Zend_Db::factory('PDO_MYSQL', $params);//$config->resources->db->params->adapter, $config.resources->db->params);
        Zend_Db_Table::setDefaultAdapter($db);
        parent::_setup();
    }
*/
}

