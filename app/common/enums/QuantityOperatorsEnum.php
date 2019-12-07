<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ١:٢٥ ص
 */

namespace app\common\enums;


class QuantityOperatorsEnum
{
    const OPERATOR_INCREMENT = 'inc';
    const OPERATOR_DECREMENT = 'dec';

    /**
     * @return array
     */
    static public function getValues(): array
    {
        return [
            self::OPERATOR_INCREMENT,
            self::OPERATOR_DECREMENT
        ];
    }
}
