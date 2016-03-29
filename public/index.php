<?php

define('FACEBOOK_PATH', realpath('../facebook-sdk') . '/');
require_once FACEBOOK_PATH . 'src/Facebook/autoload.php';

use Phalcon\Mvc\Application;
use Phalcon\Config\Adapter\Ini as ConfigIni;

try
{
	define('APP_PATH', realpath('..') . '/');

	$config = new ConfigIni(APP_PATH . 'app/config/config.ini');

	require APP_PATH . 'app/config/loader.php';

	require APP_PATH . 'app/config/services.php';

	$application = new Application($di);

	echo $application->handle()->getContent();
}
catch (Exception $e)
{
	echo $e->getMessage();
}

?>