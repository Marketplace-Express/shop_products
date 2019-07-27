<?php
namespace app\modules\cli;

use Phalcon\DiInterface;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use app\modules\cli\services\IndexingService;
use app\common\services\ProductsService;

class Module implements ModuleDefinitionInterface
{
    /**
     * Registers an autoloader related to the module
     *
     * @param DiInterface $di
     */
    public function registerAutoloaders(DiInterface $di = null)
    {
        $loader = new Loader();

        $loader->registerNamespaces([
            'app\modules\cli\tasks' => __DIR__ . '/tasks/',
        ]);

        $loader->register();
    }

    /**
     * Registers services related to the module
     *
     * @param DiInterface $di
     */
    public function registerServices(DiInterface $di)
    {
        // Register indexing service as a service
        $di->set('indexing', function() {
            return new IndexingService();
        });

        $di->set('products', function () {
            return new ProductsService();
        });
    }
}
