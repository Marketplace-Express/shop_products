<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 04:18 م
 */

namespace Shop_products\Modules\Api;

use Phalcon\DiInterface;
use Phalcon\Loader;
use \Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $dependencyInjector
     */
    public function registerAutoloaders(DiInterface $dependencyInjector = null)
    {
        $config = $dependencyInjector->getConfig();
        $loader = new Loader();
        $loader->registerNamespaces([
            'Shop_products\Modules\Api\Controllers' => __DIR__ . '/' . $config->api->version . '/controllers/'
        ]);
        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $dependencyInjector
     */
    public function registerServices(DiInterface $dependencyInjector)
    {

    }
}