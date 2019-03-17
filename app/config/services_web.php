<?php

use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;

/**
 * Registering a router
 */
$di->setShared('router', function () {
    $config = $this->getConfig();
    $router = new Router\Annotations(false);
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Index', '/api/' . $config->api->version . '/products');
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Variations', '/api/' . $config->api->version . '/variations');
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Images', '/api/' . $config->api->version . '/images');
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Rate', '/api/' . $config->api->version . '/rate');
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Questions', '/api/' . $config->api->version . '/questions');
    $router->addModuleResource('api', 'Shop_products\Modules\Api\Controllers\Search', '/api/' . $config->api->version . '/search');
    return $router;
});

/**
 * The URL component is used to generate all kinds of URLs in the application
 */
$di->setShared('url', function () {
    $config = $this->getConfig();

    $url = new UrlResolver();
    $url->setBaseUri($config->application->baseUri);

    return $url;
});

/**
 * Starts the session the first time some component requests the session service
 */
$di->setShared('session', function () {
    $session = new SessionAdapter();
    $session->start();

    return $session;
});

/**
 * Register the session flash service with the Twitter Bootstrap classes
 */
$di->set('flash', function () {
    return new Flash([
        'error'   => 'alert alert-danger',
        'success' => 'alert alert-success',
        'notice'  => 'alert alert-info',
        'warning' => 'alert alert-warning'
    ]);
});

/**
* Set the default namespace for dispatcher
*/
$di->setShared('dispatcher', function() {
    /**
     * @var \Phalcon\Events\Manager $evManager
     */
    $evManager = $this->getEventsManager();
    $evManager->attach(
        "dispatch:beforeException",
        function ($event, $dispatcher, $exception) {
            /**
             * @var Exception $exception
             * @var Dispatcher $dispatcher
             */
            switch ($exception->getCode()) {
                case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                    $dispatcher->forward([
                        'controller' => '\Shop_products\Controllers\Notfound',
                        'action'     => 'index'
                    ]);
                    return false;
                    break;
            }

            switch (true) {
                case $exception instanceof \Phalcon\Mvc\Model\Exception:
                case $exception instanceof PDOException:
                    $dispatcher->forward([
                        'controller' => '\Shop_products\Controllers\ExceptionHandler',
                        'action' => 'serverError',
                        'params' => [$exception->getMessage()]
                    ]);
                    return false;
                    break;
            }
        }
    );
    $dispatcher = new Dispatcher();
    $dispatcher->setEventsManager($evManager);
    return $dispatcher;
});
