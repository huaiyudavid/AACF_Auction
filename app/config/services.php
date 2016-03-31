<?php

use Phalcon\Cache\Frontend\Data as Frontend;
use Phalcon\Cache\Backend\Apc;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager as EventManager;
use Phalcon\Http\Response as HttpResponse;
use Phalcon\Http\Response\Cookies;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Model\Manager as ModelsManager;
use Phalcon\Mvc\Model\Transaction\Manager as TransactionManager;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlProvider;
use Phalcon\Mvc\View;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;

$di = new FactoryDefault();

$di->set('dispatcher', function() use ($di)
{
	$dispatcher = new Dispatcher;
	$eventManager = new EventManager;

	$eventManager->attach('dispatch:beforeDispatch', new AuthPlugin);
	$dispatcher->setEventsManager($eventManager);
	return $dispatcher;
});

$di->set('router', function ()
{
	$router = new Router();

	$router->removeExtraSlashes(true);

	$router->add('/item/([1-9]\d*)', array(
		'controller' => 'item',
		'action' => 'index',
		'id' => 1
	));
	$router->add('/edit/([1-9]\d*)', array(
		'controller' => 'edit',
		'action' => 'index',
		'id' => 1
	));
	$router->add('/users/([a-zA-Z0-9]+)', array(
		'controller' => 'users',
		'action' => 'index',
		'username' => 1
	));
	$router->add('/users/([a-zA-Z0-9]+)/bids', array(
		'controller' => 'users',
		'action' => 'bids',
		'username' => 1
	));
	$router->add('/users/([a-zA-Z0-9]+)/watchlist', array(
		'controller' => 'users',
		'action' => 'watchlist',
		'username' => 1
	));
	$router->add('/search/(.+)/([a-z-]+)/([a-z ]+)/([1-9]\d*)', array(
		'controller' => 'search',
		'action' => 'index',
		'query' => 1,
		'sort' => 2,
		'filter' => 3,
		'page' => 4
	));
	$router->add('/all/([a-z-]+)/([a-z ]+)/([1-9]\d*)', array(
		'controller' => 'all',
		'action' => 'index',
		'sort' => 1,
		'filter' => 2,
		'page' => 3
	));
	return $router;
});

$di->set('http', function()
{
	$response = new HttpResponse();
	return $response;
});

$di->set('url', function() use ($config)
{
	$url = new UrlProvider();
	$url->setBaseUri($config->application->baseUri);
	return $url;
});

$di->set('modelsManager', function()
{
	return new ModelsManager();
});

$di->set('view', function() use ($config)
{
	$view = new View();
	$view->setViewsDir(APP_PATH . $config->application->viewsDir);
	$view->registerEngines(array(".volt" => 'volt'));
	return $view;
});

$di->set('volt', function($view, $di)
{
	$volt = new VoltEngine($view, $di);
	$volt->setOptions(array(
		"compiledPath" => APP_PATH . "cache/volt/"
	));

	$compiler = $volt->getCompiler();
	//$compiler->addFunction('is_a', 'is_a');

	return $volt;
}, true);

$di->set('cookies', function()
{
	$cookies = new Cookies();
	$cookies->useEncryption(false);
	return $cookies;
});

$di->set('cache', function()
{
	$frontend = new Frontend(array('lifetime' => 86400));
	$cache = new Apc($frontend);
	return $cache;
});

$di->set('db', function() use ($config)
{
	return new Phalcon\Db\Adapter\Pdo\Mysql(array(
		'host' => $config->database->host,
		'dbname' => $config->database->name,
		"port" => 3306,
		'username' => $config->database->username,
		'password' => $config->database->password,
	));
});

$di->set('utils', function()
{
	return new UiUtils();
});

$di->setShared('session', function()
{
	$session = new SessionAdapter();
	$session->start();
	return $session;
});

$di->setShared('config', $config);

$di->setShared('transactions', function()
{
	return new TransactionManager();
});

?>
