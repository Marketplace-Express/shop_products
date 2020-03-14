<?php

use Ehann\RediSearch\Index;
use Ehann\RediSearch\Suggestion;
use Phalcon\Config\Adapter\Yaml;
use Phalcon\Db\Adapter\MongoDB\Client;
use Phalcon\Db\Profiler;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Logger\Factory;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;
use app\common\enums\ProductsCacheIndexesEnum;
use app\common\logger\ApplicationLogger;
use app\common\redis\Connector;
use app\common\services\user\UserService;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    $config = new Yaml(APP_PATH . '/config/config.yml', [
        '!appDir' => function ($value) {
            return APP_PATH . $value ;
        },
        '!baseDir' => function ($value) {
            return BASE_PATH . $value;
        }
    ]);
    return $config;
});

/**
 * Profiler service
 */
$di->setShared('profiler', function () {
    return new Phalcon\Db\Profiler();
});


/**
 * MySQL database connection
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    /** @var \Phalcon\Db\Adapter\Pdo $connection */
    $connection = new $class($params);

    /**
     * @var Profiler $profiler
     */
    $profiler = $this->getProfiler();
    $eventsManager = new Manager();
    $eventsManager->attach('db', function ($event, $connection) use ($profiler, $config) {
        /**
         * @var Event $event
         * @var \Phalcon\Db\Adapter\Pdo $connection
         */
        if ($event->getType() == 'beforeQuery') {
            $profiler->startProfile($connection->getSQLStatement());
        }

        if ($event->getType() == 'afterQuery') {
            $profiler->stopProfile();

            if (!file_exists($config->application->logsDir . 'db.log')) {
                touch($config->application->logsDir . 'db.log');
            }

            // Log last SQL statement
            Factory::load([
                'name' => $config->application->logsDir . 'db.log',
                'adapter' => 'file'
            ])->info($profiler->getLastProfile()->getSqlStatement());
        }
    });

    $connection->setEventsManager($eventsManager);

    return $connection;
});

/**
 * MongoDB connection
 */
$di->setShared('mongo', function () {
    $config = $this->getConfig();
    $connectionString = "mongodb://";
    if (!empty($config->mongodb->username) && !empty($config->mongodb->password)) {
        $connectionString .= $config->mongodb->username . ":" . $config->mongodb->password . "@";
    }
    $connectionString .= $config->mongodb->host . ":" . $config->mongodb->port;
    $mongo = new Client($connectionString);
    return $mongo->selectDatabase($config->mongodb->dbname);
});

$di->setShared('collectionManager', function () {
    return new \Phalcon\Mvc\Collection\Manager();
});

/**
 * Register Redis as a service
 */
$di->set('cache', function ($instance){
    $config = $this->getConfig()->cache;
    $connector = new Connector();
    $connector->connect(
        $config->$instance->host,
        $config->$instance->port,
        $config->$instance->database,
        $config->$instance->auth
    );
    return ['adapter' => $connector, 'instance' => $connector->redis];
});

/**
 * Redis instance for product cache
 */
$di->setShared('productsCache', function () {
    return $this->getCache('products_cache')['instance'];
});

$di->set('productsCacheIndex', function (){
    return new Index($this->getCache('products_cache')['adapter'],
        ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

$di->set('productsCacheSuggestion', function (){
    return new Suggestion($this->getCache('products_cache')['adapter'],
        ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

/**
 * Redis instance for product images
 */
$di->set('imagesCache', function () {
    $cache = $this->getCache('images_cache');
    return $cache['instance'];
});

/**
 * Redis instance of product questions
 */
$di->set('questionsCache', function () {
    $cache = $this->getCache('questions_cache');
    return $cache['instance'];
});

/**
 * Register questions suggestions cache
 */
$di->set('questionsCacheSuggestion', function (){
    return new Suggestion($this->getCache('questions_cache')['adapter'],
        ProductsCacheIndexesEnum::QUESTIONS_INDEX_NAME
    );
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    $metadata = new MetaDataAdapter([
        'lifetime' => 1
    ]);

    $metadata->setStrategy(
        new \Phalcon\Mvc\Model\MetaData\Strategy\Annotations()
    );

    return $metadata;
});

$di->setShared('logger', function() {
    return new ApplicationLogger();
});

/** RabbitMQ service */
$di->setShared('queue', function () {
    $config = $this->getConfig();
    $connection = new AMQPStreamConnection(
        $config->rabbitmq->host,
        $config->rabbitmq->port,
        $config->rabbitmq->username,
        $config->rabbitmq->password
    );
    $channel = $connection->channel();
    $channel->queue_declare(
        $config->rabbitmq->sync_queue->queue_name,
        false, false, false, false, false,
        new AMQPTable(['x-message-ttl' => $config->rabbitmq->sync_queue->message_ttl])
    );
    $channel->queue_declare(
        $config->rabbitmq->async_queue->queue_name,
        false, false, false, false, false,
        new AMQPTable(['x-message-ttl' => $config->rabbitmq->async_queue->message_ttl])
    );
    return $channel;
});

$di->setShared(
    'userService',
    UserService::class
);
