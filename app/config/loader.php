<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'Shop_products\Models' => APP_PATH . '/common/models/',
    'Shop_products\Collections' => APP_PATH . '/common/collections',
    'Shop_products\Controllers' => APP_PATH . '/common/controllers/',
    'Shop_products\Events' => APP_PATH . '/common/events/',
    'Shop_products\Library' => APP_PATH . '/common/library/',
    'Shop_products\Services' => APP_PATH . '/common/services/',
    'Shop_products\Services\User' => APP_PATH . '/common/services/user/',
    'Shop_products\RequestHandler' => APP_PATH . '/common/request-handler/',
    'Shop_products\RequestHandler\Product' => APP_PATH . '/common/request-handler/product/',
    'Shop_products\RequestHandler\Variation' => APP_PATH . '/common/request-handler/variation/',
    'Shop_products\RequestHandler\Question' => APP_PATH . '/common/request-handler/question/',
    'Shop_products\RequestHandler\Image' => APP_PATH . '/common/request-handler/image/',
    'Shop_products\RequestHandler\Rate' => APP_PATH . '/common/request-handler/rate/',
    'Shop_products\RequestHandler\Queue' => APP_PATH . '/common/request-handler/queue/',
    'Shop_products\Exceptions' => APP_PATH . '/common/exceptions/',
    'Shop_products\Utils' => APP_PATH . '/common/utils/',
    'Shop_products\Logger' => APP_PATH . '/common/logger/',
    'Shop_products\Traits' => APP_PATH . '/common/traits/',
    'Shop_products\Validators' => APP_PATH . '/common/validators/',
    'Shop_products\Interfaces' => APP_PATH . '/common/interfaces/',
    'Shop_products\Repositories' => APP_PATH . '/common/repositories/',
    'Shop_products\Services\Cache' => APP_PATH . '/common/services/cache/',
    'Shop_products\Enums' => APP_PATH . '/common/enums/',
    'Shop_products\Modules\Api\Controllers' => APP_PATH . '/modules/api/' . $config->api->version . '/controllers/',
    'Shop_products\Modules\Cli\Request' => APP_PATH . '/modules/cli/request/',
    'Shop_products\Modules\Cli\Services' => APP_PATH . '/modules/cli/services/',
    'Shop_products\Redis' => APP_PATH . '/common/redis/'
]);

/**
 * Register Vendors
 */
$loader->registerFiles([
    APP_PATH . '/common/library/vendor/autoload.php'
]);

/**
 * Register module classes
 */
$loader->registerClasses([
    'Shop_products\Modules\Api\Module' => APP_PATH . '/modules/api/Module.php',
    'Shop_products\Modules\Cli\Module'      => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
