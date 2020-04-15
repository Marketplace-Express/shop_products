<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 12:15 م
 */

namespace app\common\validators;


use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
use app\common\utils\UuidUtil;

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
     * @param Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(Validation $validation, $attribute)
    {
        $value = $validation->getValue($attribute);
        $allowEmpty = $this->getOption('allowEmpty', false);
        $message = $this->getOption('message', 'Invalid UUID');

        if (empty($value) && $allowEmpty) {
            return true;
        }

        if (!$this->getUuidUtil()->isValid($value)) {
            $validation->appendMessage(new Message($message, $attribute));
            return false;
        }

        return true;
    }
}