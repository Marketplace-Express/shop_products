<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 06:44 م
 */

namespace Shop_products\Controllers;

use Phalcon\Mvc\Controller;
use Shop_products\Utils\UuidUtil;

class BaseController extends Controller
{
    /** @var \JsonMapper $jsonMapper */
    protected $jsonMapper;

    /** @var UuidUtil $uuidUtil */
    protected $uuidUtil;

    protected function getJsonMapper(): \JsonMapper
    {
        return $this->jsonMapper;
    }

    protected function getUuidUtil(): UuidUtil
    {
        return $this->uuidUtil;
    }

    public function onConstruct()
    {
        $this->jsonMapper = new \JsonMapper();
        $this->uuidUtil = new UuidUtil();
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