<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 01:58 م
 */

namespace app\common\exceptions;


class OperationNotPermitted extends BaseException
{
    public function __construct($message = "", $code = 403, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
