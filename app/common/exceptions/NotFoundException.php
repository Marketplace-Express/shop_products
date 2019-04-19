<?php
/**
 * User: Wajdi Jurry
 * Date: 22/03/19
 * Time: 12:48 م
 */

namespace Shop_products\Exceptions;


use Throwable;

class NotFoundException extends \Exception
{
    public function __construct(string $message = "Not Found", int $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}