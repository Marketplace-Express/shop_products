<?php
/**
 * User: Wajdi Jurry
 * Date: 18/08/18
 * Time: 05:27 م
 */

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Loader;

ini_set("display_errors", 1);
error_reporting(E_ALL);

define("ROOT_PATH", __DIR__);

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// Required for phalcon/incubator
include __DIR__ . "/../app/common/library/vendor/autoload.php";

// Use the application autoloader to autoload the classes
// Autoload the dependencies found in composer
$loader = new Loader();
$loader->registerDirs(
    [
        ROOT_PATH
    ]
);

$loader->registerNamespaces([
    'Shop_products\Models' => ROOT_PATH . '/../app/common/models',
    'Shop_products\Controllers' => ROOT_PATH . '/../app/common/controllers',
    'Shop_products\Repositories' => ROOT_PATH . '/../app/common/repositories',
    'Shop_products\Interfaces' => ROOT_PATH . '/../app/common/interfaces/',
    'Shop_products\Traits' => ROOT_PATH . '/../app/common/traits',
    'Shop_products\Services' => ROOT_PATH . '/../app/common/services',
    'Shop_products\Services\Cache' => ROOT_PATH . '/../app/common/services/cache',
    'Shop_products\RequestHandler' => ROOT_PATH . '/../app/common/request-handler',
    'Shop_products\RequestHandler\Product' => ROOT_PATH . '/../app/common/request-handler/product',
    'Shop_products\Modules\Api\Controllers' => ROOT_PATH . '/../app/modules/api/1.0/controllers',
    'Shop_products\Utils' => ROOT_PATH . '/../app/common/utils',
    'Shop_products\Logger' => ROOT_PATH . '/common/logger/'
]);

$loader->registerClasses([
    'Shop_products\Tests\Mocks\RequestMock' => ROOT_PATH . '/mocks/RequestMock.php',
    'Shop_products\Tests\Mocks\ResponseMock' => ROOT_PATH . '/mocks/ResponseMock.php',
    'Shop_products\Exceptions\ArrayOfStringsException' => ROOT_PATH . '/../app/common/exceptions/ArrayOfStringsException.php',
    'Shop_products\Exceptions\NotFoundException' => ROOT_PATH . '/../app/common/exceptions/NotFoundException.php'
]);

$loader->register();

$di = new FactoryDefault();

Di::reset();

// Add any needed services to the DI here

Di::setDefault($di);
