<?php

/**
 * App config file
 */

defined('BASE_PATH') || define('BASE_PATH', dirname(dirname(__DIR__)));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => '172.19.0.1',
        'port' => 3306,
        'username' => 'phalcon',
        'password' => 'secret',
        'dbname' => 'shop_products',
        'charset' => 'utf8'
    ],
    'mongodb' => [
        'host' => '172.19.0.1',
        'port' => 27017,
        'username' => null,
        'password' => null,
        'dbname' => 'shop_products'
    ],
    'cache' => [
        'products_cache' => [
            'host' => '172.19.0.1',
            'port' => 6379,
            'persistent' => true,
            'database' => 0,
            'ttl' => -1,
            'auth' => null
        ],
        'images_cache' => [
            'host' => '172.19.0.1',
            'port' => 6379,
            'persistent' => true,
            'database' => 1,
            'ttl' => -1,
            'auth' => null
        ],
        'questions_cache' => [
            'host' => '172.19.0.1',
            'port' => 6379,
            'persistent' => true,
            'database' => 2,
            'ttl' => -1,
            'auth' => null
        ],
        'rates_cache' => [
            'host' => '172.19.0.1',
            'port' => 6379,
            'persistent' => true,
            'database' => 3,
            'ttl' => -1,
            'auth' => null
        ]
    ],
    'rabbitmq' => [
        'host' => '172.19.0.1',
        'port' => 5672,
        'username' => 'guest',
        'password' => 'guest',
        'sync_queue' => [
            'queue_name' => 'products_sync',
            'message_ttl' => 10000
        ],
        'async_queue' => [
            'queue_name' => 'products_async',
            'message_ttl' => 10000
        ]
    ],
    'application' => [
        'modelsDir' => APP_PATH . '/models/',
        'controllersDir' => APP_PATH . '/modules/api/controllers/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'logsDir' => APP_PATH . '/logs/',
        'uploadDir' => BASE_PATH . '/public/uploads/',
        'imgur' => [
            'apiKey' => 'fde8e22da065b65',
            'apiSecret' => '09b2fcc7e71311b414ac5b16e37191e96f303735',
            'accessToken' => '6b02a3b092cadf34e1b9a84c01ab896ff3a7e7d1'
        ],
        'token' => [
            'saltKey' => 'abc@123456789012',
            'allowedAlg' => 'HS512'
        ],
        'debug' => 1,
        'debugSql' => 1,
    ],
    'printNewLine' => true
]);