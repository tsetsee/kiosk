<?php
/**
 * Created by PhpStorm.
 * User: tsetsee
 * Date: 9/13/17
 * Time: 4:25 PM
 */

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbname' => 'kiosk',
        'host' => 'localhost',
        'port' => '3306',
        'user' => 'root',
        'password' => 'test',
        'charset' => 'UTF8',
    ),
));

$app->register(new JDesrosiers\Silex\Provider\CorsServiceProvider(), [
    "cors.allowOrigin" => "http://kiosk.dev",
]);