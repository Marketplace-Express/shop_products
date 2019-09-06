<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ٧:٥٥ م
 */

namespace app\common\enums;


class DimensionUnitsEnum
{
    const UNIT_CENTIMETERS = 'cm';
    const UNIT_INCHES = 'in';

    /**
     * @return array
     */
    static public function getAll(): array
    {
        return [
            self::UNIT_CENTIMETERS,
            self::UNIT_INCHES
        ];
    }
}
