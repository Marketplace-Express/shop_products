<?php
/**
 * User: Wajdi Jurry
 * Date: 21/03/2020
 * Time: 04:08 PM
 */

namespace app\modules\api\controllers;


use app\common\exceptions\ConnectionFailedException;
use Phalcon\Mvc\Controller;
use Webmozart\Assert\Assert;

/**
 * Class HealthCheckController
 * @package app\modules\api\controllers
 * @RoutePrefix("/api/health")
 */
class HealthCheckController extends Controller
{
    /**
     * @Get('/')
     */
    public function indexAction()
    {
        try {
            $this->checkMysqlConnection();
            $this->checkMongoConnection();
            $this->checkRabbimqConnection();
            $this->checkRedisConnection();
            return $this->response->setJsonContent(['status' => 200, 'message' => 'service is up']);
        } catch (\Throwable $exception) {
            return $this->response
                ->setJsonContent([
                    'status' => 500,
                    'message' => $exception->getMessage()
                ]);
        }
    }

    /**
     * @throws ConnectionFailedException
     */
    private function checkMysqlConnection()
    {
        if (!$this->di->getDb()->connect()) {
            throw new ConnectionFailedException('could not connect to mysql database');
        }
    }

    /**
     * @throws ConnectionFailedException
     */
    private function checkMongoConnection()
    {
        $status = $this->di->get('mongo')->command(['ping' => true])->toArray();
        $status = array_shift($status);
        if (!$status->ok) {
            throw new ConnectionFailedException('could not connect to mongo database');
        }
    }

    /**
     * @throws ConnectionFailedException
     */
    private function checkRabbimqConnection()
    {
        try {
            $connection = $this->di->getAmqp()->getChannel()->getConnection();
            $connection->checkHeartBeat();
        } catch (\Throwable $exception) {
            throw new ConnectionFailedException('could not connect to rabbitmq server');
        }
    }

    /**
     * @throws ConnectionFailedException
     */
    private function checkRedisConnection()
    {
        try {
            $productsCacheInstance = $this->di->getCache('products_cache')['instance'];
            $imagesCacheInstance = $this->di->getCache('images_cache')['instance'];
            $questionsCacheInstance = $this->di->getCache('questions_cache')['instance'];
            $ratesCacheInstance = $this->di->getCache('rates_cache')['instance'];
            Assert::eq([
                $productsCacheInstance->ping("1"),
                $imagesCacheInstance->ping("1"),
                $questionsCacheInstance->ping("1"),
                $ratesCacheInstance->ping("1")
            ], ["1", "1", "1", "1"]);
        } catch (\Throwable $exception) {
            throw new ConnectionFailedException('could not connect to redis server');
        }
    }
}