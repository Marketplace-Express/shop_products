<?php
/**
 * User: Wajdi Jurry
 * Date: ٣١‏/٨‏/٢٠١٩
 * Time: ٢:٣٧ م
 */

namespace app\common\validators\rules;


class DownloadableProductRules extends AbstractProductRules
{
    /** @var int */
    public $maxDigitalSize = 104857600; // 100 MB

    public function toArray(): array
    {
        return [];
    }
}
