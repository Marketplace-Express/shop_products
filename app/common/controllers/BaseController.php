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

    /**
     * @return JsonMapper
     */
    protected function getJsonMapper(): JsonMapper
    {
        return $this->jsonMapper;
    }

    /**
     * @return UuidUtil
     */
    protected function getUuidUtil(): UuidUtil
    {
        return $this->uuidUtil;
    }

    /**
     * @return ApplicationLogger
     */
    public function getLogger(): ApplicationLogger
    {
        return $this->logger;
    }

    public function onConstruct()
    {
        $this->jsonMapper = new JsonMapper();
        $this->jsonMapper->bEnforceMapType = false;

        $this->uuidUtil = new UuidUtil();
        $this->logger = new ApplicationLogger();
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
