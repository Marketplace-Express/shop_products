<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 12:15 م
 */

namespace Shop_products\Validators;


use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
use Shop_products\Utils\UuidUtil;

class UuidValidator extends Validator implements ValidatorInterface
{
    /** @var UuidUtil */
    private $uuidUtil;

    private function getUuidUtil()
    {
        return $this->uuidUtil ?? new UuidUtil();
    }

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
        if (!$this->getUuidUtil()->isValid($value) && !$this->getOption('allowEmpty')) {
            $validation->appendMessage(new Message($this->getOption('message', 'Invalid UUID'), $attribute));
            return false;
        }
        return true;
    }
}