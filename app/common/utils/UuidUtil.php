<?php
/**
 * User: Wajdi Jurry
 * Date: 10/09/18
 * Time: 12:26 ص
 */

namespace Shop_products\Utils;

use Ramsey\Uuid\Uuid;

class UuidUtil
{
    /**
     * @return string
     * @throws \Exception
     */
    public function uuid()
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * @param $string
     * @return bool
     */
    public function isValid($string)
    {
        return Uuid::isValid($string);
    }
}