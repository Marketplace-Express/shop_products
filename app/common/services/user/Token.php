<?php
/**
 * User: Wajdi Jurry
 * Date: 13/04/19
 * Time: 01:03 ص
 */

namespace Shop_products\Services\User;


class Token
{
    /** @var string */
    public $user_id;

    /** @var int */
    public $access_level;

    /** @var int $exp */
    public $exp;

    /** @var null|string */
    public $vendor_id = null;
}