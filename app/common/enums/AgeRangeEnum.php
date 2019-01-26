<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 03:48 م
 */

namespace Shop_products\Enums;


class AgeRangeEnum
{
    const AGE_RANGE_CHILDREN = 'children';
    const AGE_RANGE_YOUTH = 'youth';
    const AGE_RANGE_ADULTS = 'adults';
    const AGE_RANGE_OLDER = 'older';
    const AGE_RANGE_ALL = 'all';

    public static function getKeys()
    {
        return [
            self::AGE_RANGE_CHILDREN,
            self::AGE_RANGE_YOUTH,
            self::AGE_RANGE_ADULTS,
            self::AGE_RANGE_OLDER,
            self::AGE_RANGE_ALL
        ];
    }

    public static function getValues()
    {
        return [
            self::AGE_RANGE_CHILDREN => [1, 14],
            self::AGE_RANGE_YOUTH => [15, 24],
            self::AGE_RANGE_ADULTS => [25, 64],
            self::AGE_RANGE_OLDER => [64, 90],
            self::AGE_RANGE_ALL => [1, 90]
        ];
    }
}