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
    $config = new Yaml(CONFIG_PATH . '/products.yml', [
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
$di->setShared('cache', function (){
    $config = $this->getConfig()->cache;
    $redisInstance = new Connector();
    $redisInstance->connect(
        $config->products_cache->host,
        $config->products_cache->port,
        $config->products_cache->database,
        $config->products_cache->auth
    );
    return ['adapter' => $redisInstance, 'instance' => $redisInstance->redis];
});

/**
 * Redis instance for product cache
 */
$di->setShared('productsCache', function () {
    return $this->getCache()['instance'];
});

$di->setShared('productsCacheIndex', function (){
    return new Index($this->getCache()['adapter'],
        ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

$di->set('productsCacheSuggestion', function (){
    return new Suggestion($this->getCache()['adapter'],
        ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

/**
 * Redis instance for product images
 */
$di->setShared('imagesCache', function () {
    $config = $this->getConfig();
    $redis = new Redis();
    if (!empty($auth = $config->cache->images_cache->auth)) {
        $redis->auth($auth);
    }
    $redis->pconnect(
        $config->cache->images_cache->host,
        $config->cache->images_cache->port
    );
    $redis->select($config->cache->images_cache->database);
    return $redis;
});

/**
 * Redis instance of product questions
 */
$di->setShared('questionsCache', function () {
    $config = $this->getConfig()->cache;
    $redisInstance = new Connector();
    $redisInstance->connect(
        $config->questions_cache->host,
        $config->questions_cache->port,
        $config->questions_cache->database,
        $config->questions_cache->auth
    );
    return ['adapter' => $redisInstance, 'instance' => $redisInstance->redis];
});

$di->setShared('questionsCacheInstance', function () {
    return $this->getQuestionsCache()['instance'];
});

/**
 * Register questions suggestions cache
 */
$di->set('questionsCacheSuggestion', function (){
    return new Suggestion($this->getQuestionsCache()['adapter'],
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
