<?php

/**
 * App config file
 */

defined('BASE_PATH') || define('BASE_PATH', dirname(dirname(__DIR__)));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

/**
 * Register Vendors
 */
$loader = new \Phalcon\Loader();
$loader->registerFiles([
    APP_PATH . '/vendor/autoload.php'
])->loadFiles();

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

exit(var_dump($_ENV));

return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => $_ENV['MYSQL_HOST'],
        'port' => $_ENV['MYSQL_PORT'],
        'username' => $_ENV['MYSQL_USER'],
        'password' => $_ENV['MYSQL_PASSWORD'],
        'dbname' => $_ENV['MYSQL_DATABASE'],
        'charset' => $_ENV['MYSQL_CHARSET']
    ],
    'mongodb' => [
        'host' => $_ENV['MONGODB_HOST'],
        'port' => $_ENV['MONGODB_PORT'],
        'username' => $_ENV['MONGODB_USER'],
        'password' => $_ENV['MONGODB_PASSWORD'],
        'dbname' => $_ENV['MONGODB_DATABASE']
    ],
    'cache' => [
        'products_cache' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'persistent' => $_ENV['PRODUCTS_CACHE_PERSISTENT'],
            'database' => $_ENV['PRODUCTS_CACHE_DB'],
            'ttl' => $_ENV['PRODUCTS_CACHE_TTL'],
            'auth' => $_ENV['REDIS_AUTH']
        ],
        'images_cache' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'persistent' => $_ENV['IMAGES_CACHE_PERSISTENT'],
            'database' => $_ENV['IMAGES_CACHE_DB'],
            'ttl' => $_ENV['IMAGES_CACHE_TTL'],
            'auth' => $_ENV['REDIS_AUTH']
        ],
        'questions_cache' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'persistent' => $_ENV['QUESTIONS_CACHE_PERSISTENT'],
            'database' => $_ENV['QUESTIONS_CACHE_DB'],
            'ttl' => $_ENV['QUESTIONS_CACHE_TTL'],
            'auth' => $_ENV['REDIS_AUTH']
        ],
        'rates_cache' => [
            'host' => $_ENV['REDIS_HOST'],
            'port' => $_ENV['REDIS_PORT'],
            'persistent' => $_ENV['RATES_CACHE_PERSISTENT'],
            'database' => $_ENV['RATES_CACHE_DB'],
            'ttl' => $_ENV['RATES_CACHE_TTL'],
            'auth' => $_ENV['REDIS_AUTH']
        ]
    ],
    'rabbitmq' => [
        'host' => $_ENV['RABBITMQ_HOST'],
        'port' => $_ENV['RABBITMQ_PORT'],
        'username' => $_ENV['RABBITMQ_USER'],
        'password' => $_ENV['RABBITMQ_PASSWORD'],
        'sync_queue' => [
            'queue_name' => $_ENV['RABBITMQ_SYNC_QUEUE_NAME'],
            'message_ttl' => $_ENV['RABBITMQ_SYNC_MESSAGE_TTL']
        ],
        'async_queue' => [
            'queue_name' => $_ENV['RABBITMQ_ASYNC_QUEUE_NAME'],
            'message_ttl' => $_ENV['RABBITMQ_ASYNC_MESSAGE_TTL']
        ]
    ],
    'application' => [
        'modelsDir' => APP_PATH . '/common/models/',
        'controllersDir' => APP_PATH . '/modules/api/controllers/',
        'migrationsDir' => APP_PATH . '/migrations/',
        'logsDir' => APP_PATH . '/logs/',
        'uploadDir' => BASE_PATH . '/public/uploads/',
        'imgur' => [
            'apiKey' => $_ENV['IMGUR_API_KEY'],
            'apiSecret' => $_ENV['IMGUR_API_SECRET'],
            'accessToken' => $_ENV['IMGUR_ACCESS_TOKEN']
        ],
        'debug' => $_ENV['APP_DEBUG'],
        'debugSql' => $_ENV['APP_DEBUG_SQL'],
        'api' => [
            'base_uri' => $_ENV['API_HOST'],
            'timeout' => $_ENV['API_TIMEOUT']
        ]
    ],
    'printNewLine' => true
]);