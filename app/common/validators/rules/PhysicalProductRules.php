<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ٣:٠٥ م
 */

namespace app\common\validators\rules;


class PhysicalProductRules extends ProductRules
{

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), []);
    }
}
