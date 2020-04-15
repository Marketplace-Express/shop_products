<?php
/**
 * User: Wajdi Jurry
 * Date: 19/10/18
 * Time: 04:49 م
 */

namespace app\common\exceptions;


class OperationFailed extends BaseException
{
    public function __construct($messages, int $code = 503, \Throwable $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
