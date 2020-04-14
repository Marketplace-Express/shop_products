<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'app\common\models' => APP_PATH . '/common/models/',
    'app\common\models\behavior' => APP_PATH . '/common/models/behaviors/',
    'app\common\models\resultset' => APP_PATH . '/common/models/resultset/',
    'app\common\models\embedded\physical' => APP_PATH . '/common/models/embedded/physical',
    'app\common\models\embedded\downloadable' => APP_PATH . '/common/models/embedded/downloadable',
    'app\common\models\sorting' => APP_PATH . '/common/models/sorting',
    'app\common\models\factory' => APP_PATH . '/common/models/factory',
    'app\common\collections' => APP_PATH . '/common/collections',
    'app\common\events\middleware' => APP_PATH . '/common/events/middleware/',
    'app\common\services' => APP_PATH . '/common/services/',
    'app\common\services\user' => APP_PATH . '/common/services/user/',
    'app\common\services\cache' => APP_PATH . '/common/services/cache/',
    'app\common\requestHandler' => APP_PATH . '/common/requestHandler/',
    'app\common\requestHandler\product' => APP_PATH . '/common/requestHandler/product/',
    'app\common\requestHandler\variation' => APP_PATH . '/common/requestHandler/variation/',
    'app\common\requestHandler\question' => APP_PATH . '/common/requestHandler/question/',
    'app\common\requestHandler\image' => APP_PATH . '/common/requestHandler/image/',
    'app\common\requestHandler\rate' => APP_PATH . '/common/requestHandler/rate/',
    'app\common\requestHandler\queue' => APP_PATH . '/common/requestHandler/queue/',
    'app\common\exceptions' => APP_PATH . '/common/exceptions/',
    'app\common\utils' => APP_PATH . '/common/utils/',
    'app\common\logger' => APP_PATH . '/common/logger/',
    'app\common\traits' => APP_PATH . '/common/traits/',
    'app\common\validators' => APP_PATH . '/common/validators/',
    'app\common\validators\rules' => APP_PATH . '/common/validators/rules/',
    'app\common\interfaces' => APP_PATH . '/common/interfaces/',
    'app\common\repositories' => APP_PATH . '/common/repositories/',
    'app\common\enums' => APP_PATH . '/common/enums/',
    'app\modules\api\controllers' => APP_PATH . '/modules/api/controllers/',
    'app\modules\cli\request' => APP_PATH . '/modules/cli/request/',
    'app\modules\cli\services' => APP_PATH . '/modules/cli/services/',
    'app\common\redis' => APP_PATH . '/common/redis/'
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
    'app\modules\api\Module' => APP_PATH . '/modules/api/Module.php',
    'app\modules\cli\Module'      => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
