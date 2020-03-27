<?php
/**
 * User: Wajdi Jurry
 * Date: 11/01/19
 * Time: 06:44 م
 */

namespace app\modules\api\controllers;

use Phalcon\Mvc\Controller;
use app\common\utils\UuidUtil;

class BaseController extends Controller
{
    /** @var UuidUtil */
    protected $uuidUtil;

    /**
     * @return UuidUtil
     */
    protected function getUuidUtil(): UuidUtil
    {
        return $this->uuidUtil;
    }

    public function onConstruct()
    {
        $this->uuidUtil = new UuidUtil();
    }

    public function handleError(string $message, $code = 500)
    {
        $this->dispatcher->forward([
            'namespace' => 'app\modules\api\controllers',
            'controller' => 'exceptionHandler',
            'action' => 'raiseError',
            'params' => [$message, $code]
        ]);
    }
}
