<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٣:٠٨ م
 */

namespace app\common\exceptions;


use Throwable;

class ValidationFailed extends BaseException
{
    public function __construct($messages = "validation failed", $code = 400, Throwable $previous = null)
    {
        parent::__construct($messages, $code, $previous);
    }
}
