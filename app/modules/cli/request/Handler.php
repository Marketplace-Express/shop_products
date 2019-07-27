<?php
/**
 * User: Wajdi Jurry
 * Date: 22/02/19
 * Time: 04:40 م
 */

namespace app\modules\cli\request;

use Exception;
use Phalcon\Di\Injectable;

class Handler extends Injectable
{
    /** @var mixed */
    protected $service;

    /** @var string */
    protected $method;

    /** @var array */
    protected $params;

    /**
     * RequestHandler constructor.
     *
     * @param string $service
     * @param string $method
     * @param array $params
     * @param array $serviceArgs
     *
     * @throws Exception
     */
    public function __construct(string $service, array $serviceArgs, string $method, array $params = [])
    {
        $this->service = $this->getDI()->get($service, $serviceArgs);
        if (!is_callable([$this->service, $method])) {
            throw new Exception('Method "' . get_class($this->service) . '::' . $method . '" is not a callable method');
        }
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function call()
    {
        return call_user_func_array([$this->service, $this->method], $this->params);
    }
}