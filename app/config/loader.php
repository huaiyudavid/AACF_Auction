<?php

$loader = new Phalcon\Loader();

$loader->registerDirs(
	array(
		APP_PATH . $config->application->controllersDir,
		APP_PATH . $config->application->helpersDir,
		APP_PATH . $config->application->modelsDir,
		APP_PATH . $config->application->formsDir,
		APP_PATH . $config->application->pluginsDir
	)
)->register();

?>
