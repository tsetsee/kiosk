<?php

// web/index.php
require_once __DIR__.'/vendor/autoload.php';

$app = new Silex\Application();

require __DIR__ . '/config/config.php';
require __DIR__ . '/src/routing.php';

$app['debug'] = true;
$app["cors-enabled"]($app);

$app->run();