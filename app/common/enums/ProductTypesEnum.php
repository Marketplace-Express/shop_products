<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 11:08 ص
 */

namespace app\common\enums;


class ProductTypesEnum
{
    const TYPE_PHYSICAL = 'physical';
    const TYPE_DOWNLOADABLE = 'downloadable';

    public static function getValues()
    {
        return [
            self::TYPE_PHYSICAL,
            self::TYPE_DOWNLOADABLE
        ];
    }
}