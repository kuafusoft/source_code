<?php

defined('ROOT_PATH')
    || define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
	
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

defined('UPLOAD_ROOT')
    || define('UPLOAD_ROOT', realpath(APPLICATION_PATH . '/upload'));

defined('EXPORT_ROOT')
    || define('EXPORT_ROOT', realpath(APPLICATION_PATH . '/export'));

defined('REPORT_ROOT')
    || define('REPORT_ROOT', realpath(APPLICATION_PATH . '/report'));

defined('LOG_ROOT')
    || define('LOG_ROOT', realpath(APPLICATION_PATH . '/log'));

defined('JS_ROOT')
    || define('JS_ROOT', realpath(dirname(__FILE__) . '/js'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Session.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

date_default_timezone_set('Asia/Chongqing');
$application->bootstrap()
            ->run();