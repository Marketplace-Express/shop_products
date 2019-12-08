<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٥:٤٨ م
 */

namespace app\common\validators\rules;


class CommonVariationRules extends RulesAbstract
{
    /** @var int */
    public $minQuantity = 1;

    public function toArray(): array
    {
        return [];
    }
}
