<?php
/**
 * User: Wajdi Jurry
 * Date: 15/02/19
 * Time: 01:27 ص
 */

namespace Shop_products\Utils;


class DigitalUnitsConverterUtil
{
    /**
     * @param float|null $size
     * @return int
     */
    public static function mbToBytes(?float $size)
    {
        return $size ? (int) $size * 1024 * 1024 : 0;
    }

    /**
     * @param int|null $size
     * @return float|int
     */
    public static function bytesToMb(?int $size)
    {
        return $size ? $size / 1024 / 1024 : 0;
    }
}