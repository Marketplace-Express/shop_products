<?php
/**
 * User: Wajdi Jurry
 * Date: 12/04/19
 * Time: 10:47 م
 */

namespace Shop_products\Services\User;


class UserService
{
    /** @var string */
    public $userId;

    /** @var int */
    public $accessLevel = 0;

    /** @var bool */
    public $isAdmin = false;

    /** @var bool */
    public $isSuperVisor = false;
}