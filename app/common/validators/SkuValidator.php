<?php
/**
 * User: Wajdi Jurry
 * Date: 3/28/20
 * Time: 4:24 PM
 */

namespace app\common\validators;


use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class SkuValidator extends Validator
{
    /**
     * @inheritDoc
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $allowEmpty = $this->getOption('allowEmpty');
        $value = $validation->getValue($attribute);

        if (!$allowEmpty && empty($value)) {
            $validation->appendMessage(new Message('SKU is required', $attribute));
            return false;
        }

        preg_match('/[A-Za-z0-9]+[^\s]/i', $value, $formattedValue);

        if ($value !== array_shift($formattedValue)) {
            $validation->appendMessage(new Message('SKU may contain numbers, letters or both', $attribute));
            return false;
        }

        return true;
    }
}