<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 * NOTE: please remove this comment.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'version' => '1.0',
    'api' => [
        'version' => '1.0'
    ],

    'database' => [
        'adapter'  => 'Mysql',
        'host'     => '172.17.0.5',
        'username' => 'phalcon',
        'password' => 'secret',
        'dbname'   => 'shop_products',
        'charset'  => 'utf8'
    ],

    'mongodb' => [
        'host' => '172.17.0.4',
        'username' => '',
        'password' => '',
        'port' => '27017',
        'dbname' => 'shop_products'
    ],

    'cache' => [
        'productsCache' => [
            'host' => '172.17.0.2',
            'port' => 6379,
            'persistent' => true,
            'database' => 2,
            'ttl' => -1,
            'auth' => ''
        ],
        'productsVariationCache' => [
            'host' => '172.17.0.2',
            'port' => 6379,
            'persistent' => true,
            'database' => 3,
            'ttl' => -1,
            'auth' => ''
        ]
    ],

    'rabbitmq' => [
        'host' => '172.17.0.3',
        'port' => '5672',
        'username' => 'guest',
        'password' => 'guest'
    ],

    'application' => [
        'appDir'         => APP_PATH . '/',
        'modelsDir'      => APP_PATH . '/common/models/',
        'collectionsDir' => APP_PATH . '/common/collections/',
        'migrationsDir'  => APP_PATH . '/migrations/',
        'logsDir'        => APP_PATH . '/logs/',

        'validation' => [
            'productTitle' => [
                'whiteSpace' => true,
                'underscore' => true,
                'min' => 3,
                'max' => 200
            ],
            'downloadable' => [
                'maxDigitalSize' => 104857600, // bytes = 100 Mb
            ]
        ],

        // This allows the baseUri to be understand project paths that are not in the root directory
        // of the webpspace.  This will break if the public/index.php entry point is moved or
        // possibly if the web server rewrite rules are changed. This can also be set to a static path.
        'baseUri'        => preg_replace('/public([\/\\\\])index.php$/', '', $_SERVER["PHP_SELF"]),
    ],

    /**
     * if true, then we print a new line at the end of each CLI execution
     *
     * If we dont print a new line,
     * then the next command prompt will be placed directly on the left of the output
     * and it is less readable.
     *
     * You can disable this behaviour if the output of your application needs to don't have a new line at end
     */
    'printNewLine' => true
]);
