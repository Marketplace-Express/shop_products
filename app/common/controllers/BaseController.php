<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 06:44 م
 */

namespace Shop_products\Controllers;

use Phalcon\Mvc\Controller;
use Shop_products\Logger\ApplicationLogger;
use Shop_products\Utils\UuidUtil;

class BaseController extends Controller
{
    /** @var \JsonMapper */
    protected $jsonMapper;

    /** @var UuidUtil */
    protected $uuidUtil;

    /** @var ApplicationLogger */
    protected $logger;

    protected function getJsonMapper(): \JsonMapper
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
        $this->jsonMapper = new \JsonMapper();
        $this->uuidUtil = new UuidUtil();
        $this->logger = new ApplicationLogger();
    }

    /**
     * @param array $params
     * @return \stdClass
     */
    protected function queryStringToObject(array $params)
    {
        $object = new \stdClass();
        unset($params['_url']);
        foreach ($params as $key => $value) {
            $object->$key = $value;
        }
        return $object;
    }

    public function handleError(string $message, $code = 500)
    {
        $this->dispatcher->forward([
            'namespace' => 'Shop_products\Controllers',
            'controller' => 'exceptionHandler',
            'action' => 'raiseError',
            'params' => [$message, $code]
        ]);
    }
}