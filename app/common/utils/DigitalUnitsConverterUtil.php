<?php
/**
 * User: Wajdi Jurry
 * Date: 15/02/19
 * Time: 01:27 ص
 */

namespace app\common\utils;


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
        return $size ? round($size / 1024 / 1024, 2, PHP_ROUND_HALF_UP) : 0;
    }
}