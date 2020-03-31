<?php

use Phalcon\Events\Event;
use Phalcon\Events\Manager;
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
    $router->addModuleResource('api', 'app\modules\api\controllers\Products', '/api/products');
    $router->addModuleResource('api', 'app\modules\api\controllers\Variations', '/api/variations');
    $router->addModuleResource('api', 'app\modules\api\controllers\Images', '/api/images');
    $router->addModuleResource('api', 'app\modules\api\controllers\Rate', '/api/rate');
    $router->addModuleResource('api', 'app\modules\api\controllers\Questions', '/api/questions');
    $router->addModuleResource('api', 'app\modules\api\controllers\Search', '/api/search');
    $router->addModuleResource('api', 'app\modules\api\controllers\HealthCheck', '/api/health');
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
    /** @var Manager $evManager */
    $evManager = $this->getEventsManager();
    $evManager->attach("dispatch:beforeDispatch", function (Event $event, Dispatcher $dispatcher) {
        try {
            $methodReflection = new ReflectionMethod(
                $dispatcher->getControllerClass(),
                $dispatcher->getActiveMethod()
            );
            foreach ($methodReflection->getParameters() as $parameter) {
                $parameterClass = $parameter->getClass();
                if ($parameterClass instanceof ReflectionClass) {
                    $dispatcher->setParam($parameter->name, new $parameterClass->name);
                }
            }
        } catch (Exception $exception) {
            throw new \Exception('', Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
        }
    });
    $evManager->attach(
        "dispatch:beforeExecuteRoute",
        new \Sid\Phalcon\AuthMiddleware\Event()
    );
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
                        'namespace' => 'app\modules\api\controllers',
                        'controller' => 'Notfound'
                    ]);
                    return false;
                    break;
            }

            switch (true) {
                case $exception instanceof \Phalcon\Mvc\Model\Exception:
                case $exception instanceof PDOException:
                    $dispatcher->forward([
                        'controller' => '\app\common\controllers\ExceptionHandler',
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