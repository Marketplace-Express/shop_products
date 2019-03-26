<?php

use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
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
     * @var \Phalcon\Db\Profiler $profiler
     */
    $profiler = $this->getProfiler();
    $eventsManager = new \Phalcon\Events\Manager();
    $eventsManager->attach('db', function ($event, $connection) use ($profiler, $config) {
        /**
         * @var \Phalcon\Events\Event $event
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
            \Phalcon\Logger\Factory::load([
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
    $mongo = new \Phalcon\Db\Adapter\MongoDB\Client($connectionString);
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
    $redisInstance = new \Shop_products\Redis\Connector();
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
    return new \Ehann\RediSearch\Index($this->getCache()['adapter'],
        \Shop_products\Enums\ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

$di->set('productsCacheSuggestion', function (){
    return new \Ehann\RediSearch\Suggestion($this->getCache()['adapter'],
        \Shop_products\Enums\ProductsCacheIndexesEnum::PRODUCT_INDEX_NAME
    );
});

/**
 * Redis instance for product variation cache
 */
$di->setShared('productsVariationsCache', function () {
    $config = $this->getConfig();
    $redis = new Redis();
    if (!empty($auth = $config->cache->productsVariationsCache->auth)) {
        $redis->auth($auth);
    }
    $redis->pconnect(
        $config->cache->productsVariationsCache->host,
        $config->cache->productsVariationsCache->port
    );
    $redis->select($config->cache->productsVariationsCache->database);
    return $redis;
});

/**
 * If the configuration specify the use of metadata adapter use it or use memory otherwise
 */
$di->setShared('modelsMetadata', function () {
    return new MetaDataAdapter([
        'lifetime' => 1
    ]);
});

$di->setShared('logger', function() {
    return new \Shop_products\Logger\ApplicationLogger();
});

/** RabbitMQ service */
$di->setShared('queue', function () {
    $config = $this->getConfig();
    $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection(
        $config->rabbitmq->host,
        $config->rabbitmq->port,
        $config->rabbitmq->username,
        $config->rabbitmq->password
    );
    $channel = $connection->channel();
    $channel->queue_declare(
        $config->rabbitmq->sync_queue->queue_name,
        false, false, false, false, false,
        new \PhpAmqpLib\Wire\AMQPTable(['x-message-ttl' => $config->rabbitmq->sync_queue->message_ttl])
    );
    $channel->queue_declare(
        $config->rabbitmq->async_queue->queue_name,
        false, false, false, false, false,
        new \PhpAmqpLib\Wire\AMQPTable(['x-message-ttl' => $config->rabbitmq->async_queue->message_ttl])
    );
    return $channel;
});