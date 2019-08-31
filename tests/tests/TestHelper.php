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
    'app\common\models' => ROOT_PATH . '/../app/common/models',
    'app\common\controllers' => ROOT_PATH . '/../app/common/controllers',
    'app\common\repositories' => ROOT_PATH . '/../app/common/repositories',
    'app\common\interfaces' => ROOT_PATH . '/../app/common/interfaces/',
    'app\common\traits' => ROOT_PATH . '/../app/common/traits',
    'app\common\services' => ROOT_PATH . '/../app/common/services',
    'app\common\services\cache' => ROOT_PATH . '/../app/common/services/cache',
    'app\common\requestHandler' => ROOT_PATH . '/../app/common/requestHandler',
    'app\common\requestHandler\product' => ROOT_PATH . '/../app/common/requestHandler/product',
    'app\modules\api\controllers' => ROOT_PATH . '/../app/modules/api/1.0/controllers',
    'app\common\utils' => ROOT_PATH . '/../app/common/utils',
    'app\common\logger' => ROOT_PATH . '/common/logger/'
]);

$loader->registerClasses([
    'Shop_products\Tests\Mocks\RequestMock' => ROOT_PATH . '/mocks/RequestMock.php',
    'Shop_products\Tests\Mocks\ResponseMock' => ROOT_PATH . '/mocks/ResponseMock.php',
    'app\common\exceptions\OperationFailed' => ROOT_PATH . '/../app/common/exceptions/OperationFailed.php',
    'app\common\exceptions\NotFound' => ROOT_PATH . '/../app/common/exceptions/NotFound.php'
]);

$loader->register();

$di = new FactoryDefault();

Di::reset();

// Add any needed services to the DI here

Di::setDefault($di);
