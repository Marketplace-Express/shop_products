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
    /**
     * @param string $service
     * @param $serviceArgs
     * @param string $method
     * @param $data
     * @return mixed
     *
     * @throws Exception
     */
    static public function process(string $service, $serviceArgs, string $method, $data)
    {
        $service = \Phalcon\Di::getDefault()->get($service, $serviceArgs);
        if (!is_callable([$service, $method])) {
            throw new Exception('Method "' . get_class($service) . '::' . $method . '" is not a callable method');
        }
        return call_user_func_array([$service, $method], $data);
    }
}