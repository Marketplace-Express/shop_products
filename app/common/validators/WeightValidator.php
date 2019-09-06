<?php
/**
 * User: Wajdi Jurry
 * Date: ٦‏/٩‏/٢٠١٩
 * Time: ٣:١٣ م
 */

namespace app\common\validators;


use app\common\enums\WeightUnitsEnum;
use app\common\models\embedded\physical\Weight;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class WeightValidator extends Validator
{

    /**
     * Executes the validation
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(\Phalcon\Validation $validation, $attribute)
    {
        /** @var Weight $value */
        $value = $validation->getValue($attribute);
        $allowEmpty = (bool) $this->getOption('allowEmpty');
        if ($allowEmpty && empty($value)) {
            return true;
        }

        if (!filter_var($value->amount, FILTER_VALIDATE_FLOAT)) {
            $validation->appendMessage(new Message('Invalid weight amount', 'amount'));
            return false;
        }

        if (!in_array($value->unit, WeightUnitsEnum::getAll())) {
            $validation->appendMessage(new Message('Invalid weight unit', 'unit'));
            return false;
        }

        return true;
    }
}
