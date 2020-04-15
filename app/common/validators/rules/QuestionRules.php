<?php
/**
 * User: Wajdi Jurry
 * Date: ١٧‏/٨‏/٢٠١٩
 * Time: ١١:٥٥ ص
 */

namespace app\common\validators\rules;


class QuestionRules extends RulesAbstract
{
    /** @var int */
    public $minTextLength = 10;

    /** @var int */
    public $maxTextLength = 200;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
