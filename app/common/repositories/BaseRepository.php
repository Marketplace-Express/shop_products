<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٧:٢٢ م
 */

namespace app\common\repositories;


abstract class BaseRepository
{
    static public function getInstance()
    {
        return new static();
    }
}
