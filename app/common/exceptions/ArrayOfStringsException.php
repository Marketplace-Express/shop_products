<?php
/**
 * User: Wajdi Jurry
 * Date: 19/10/18
 * Time: 04:49 م
 */

namespace app\common\exceptions;

class ArrayOfStringsException extends \Exception
{
    public function __construct(array $message = [], int $code = 0)
    {
        $this->message = json_encode($message);
        $this->code = $code;
        parent::__construct($this->message, $this->code);
    }
}