<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٧:٢٢ م
 */

namespace app\common\repositories;


abstract class BaseRepository
{
    private static $repository;

    static public function getInstance(): self
    {
        return self::$repository ?? self::$repository = new static();
    }
}
