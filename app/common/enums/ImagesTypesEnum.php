<?php
/**
 * User: Wajdi Jurry
 * Date: ١١‏/٤‏/٢٠٢٠
 * Time: ٢:٢١ م
 */

namespace app\common\enums;


class ImagesTypesEnum
{
    const TYPE_PRODUCT = 'product';
    const TYPE_VARIATION = 'variation';
    const TYPE_RATE = 'rate';

    /**
     * @return array
     */
    public static function getValues(): array
    {
        return [
            self::TYPE_PRODUCT,
            self::TYPE_VARIATION,
            self::TYPE_RATE
        ];
    }
}