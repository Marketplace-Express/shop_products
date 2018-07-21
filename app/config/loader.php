<?php

use Phalcon\Loader;

$loader = new Loader();

/**
 * Register Namespaces
 */
$loader->registerNamespaces([
    'Shop_products\Models' => APP_PATH . '/common/models/',
    'Shop_products'        => APP_PATH . '/common/library/',
]);

/**
 * Register module classes
 */
$loader->registerClasses([
    'Shop_products\Modules\Frontend\Module' => APP_PATH . '/modules/frontend/Module.php',
    'Shop_products\Modules\Cli\Module'      => APP_PATH . '/modules/cli/Module.php'
]);

$loader->register();
