<?php
/**
 * User: Wajdi Jurry
 * Date: ١١‏/٤‏/٢٠٢٠
 * Time: ٧:١٣ م
 */

namespace app\common\validators\rules;


class RateRules extends RulesAbstract
{
    /** @var int */
    public $minStars = 1;

    /** @var int */
    public $maxStars = 5;

    /** @var bool */
    public $allowEmptyText = true;

    /** @var int */
    public $maxTextLength = 1000;

    /** @var int */
    public $maxNumOfImages = 5;

    public function toArray(): array
    {
        return [];
    }
}