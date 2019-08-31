<?php
/**
 * User: Wajdi Jurry
 * Date: 22/03/19
 * Time: 12:48 م
 */

namespace app\common\exceptions;


class NotFound extends BaseException
{
    public function __construct(string $message = "entity not found", int $code = 404, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
