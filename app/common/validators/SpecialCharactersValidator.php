<?php
/**
 * User: Wajdi Jurry
 * Date: ٦‏/٩‏/٢٠١٩
 * Time: ٣:٤٢ م
 */

namespace app\common\validators;


use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class SpecialCharactersValidator extends Validator
{
    const SPECIAL_CHARACTERS_PATTERN = '/[\\!@#$%^&*?{}<>]/';

    /**
     * Executes the validation
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        $value = $validation->getValue($attribute);
        $allowEmpty = $this->getOption('allowEmpty', true);
        if ($allowEmpty && empty($value)) {
            return true;
        }
        if (preg_match(self::SPECIAL_CHARACTERS_PATTERN, $value)) {
            $validation->appendMessage(new Message('Including special characters', $attribute));
            return false;
        }
        return true;
    }
}
