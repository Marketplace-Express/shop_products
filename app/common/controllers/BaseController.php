<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 06:44 م
 */

namespace app\common\controllers;

use JsonMapper;
use Phalcon\Mvc\Controller;
use app\common\logger\ApplicationLogger;
use app\common\utils\UuidUtil;
use stdClass;

class BaseController extends Controller
{
    /** @var JsonMapper */
    protected $jsonMapper;

    /** @var UuidUtil */
    protected $uuidUtil;

    /** @var ApplicationLogger */
    protected $logger;

    protected function getJsonMapper(): JsonMapper
    {
        return $this->jsonMapper;
    }

    protected function getUuidUtil(): UuidUtil
    {
        return $this->uuidUtil;
    }

    public function getLogger(): ApplicationLogger
    {
        return $this->logger;
    }

    public function onConstruct()
    {
        $this->jsonMapper = new JsonMapper();
        $this->uuidUtil = new UuidUtil();
        $this->logger = new ApplicationLogger();
    }

    /**
     * @param array $params
     * @return stdClass
     */
    protected function queryStringToObject(array $params)
    {
        $object = new stdClass();
        unset($params['_url']);
        foreach ($params as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }

    public function handleError(string $message, $code = 500)
    {
        $this->dispatcher->forward([
            'namespace' => 'app\common\controllers',
            'controller' => 'exceptionHandler',
            'action' => 'raiseError',
            'params' => [$message, $code]
        ]);
    }
}