<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ٧:٥٩ م
 */

namespace app\common\enums;


class WeightUnitsEnum
{
    const UNIT_LBS = 'lb';
    const UNIT_KG = 'kg';
    const UINT_OUNCES = 'ounces';

    /**
     * @return array
     */
    static public function getAll(): array
    {
        return [
            self::UNIT_KG,
            self::UNIT_LBS,
            self::UINT_OUNCES
        ];
    }
}
