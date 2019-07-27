<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 04:17 م
 */

namespace app\common\enums;


class GenderEnum
{
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_UNKNOWN = 'unknown';
    const GENDER_ALL = 'all';

    public static function getValues()
    {
        return [
            self::GENDER_MALE,
            self::GENDER_FEMALE,
            self::GENDER_UNKNOWN,
            self::GENDER_ALL
        ];
    }
}