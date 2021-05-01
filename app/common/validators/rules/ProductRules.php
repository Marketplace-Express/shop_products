<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ٢:٤٥ م
 */

namespace app\common\validators\rules;


class ProductRules extends RulesAbstract
{
    /** @var \stdClass */
    public $productTitle;

    public $minQuantity = 0;

    public function __construct()
    {
        $this->productTitle = new class {
            public $whiteSpace = true;
            public $underscore = true;
            public $min = 10;
            public $max = 200;
        };
    }

    public function toArray(): array
    {
        return [];
    }
}
