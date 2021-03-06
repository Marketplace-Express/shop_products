<?php
/**
 * User: Wajdi Jurry
 * Date: ٦‏/٩‏/٢٠١٩
 * Time: ٣:١٨ م
 */

namespace app\common\validators;


use app\common\enums\DimensionUnitsEnum;
use app\common\models\embedded\physical\Package;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;

class PackageValidator extends Validator
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
        /** @var Package $value */
        $value = $validation->getValue($attribute);
        $allowEmpty = (bool) $this->getOption('allowEmpty');

        if ($allowEmpty && empty($value)) {
            return true;
        }

        if (!$value instanceof Package) {
            $validation->appendMessage(new Message('You should provide a valid package dimensions', 'package'));
            return false;
        }

        $dimensions = $value->dimensions;
        $unit = $value->unit;

        $dimensions = array_filter((array) $dimensions, function ($dimension) {
            return filter_var($dimension, FILTER_VALIDATE_FLOAT);
        });

        if (empty($dimensions)) {
            $validation->appendMessage(new Message('Invalid dimensions values', 'dimensions'));
            return false;
        }

        if (!in_array($unit, DimensionUnitsEnum::getAll())) {
            $validation->appendMessage(new Message('Invalid dimension unit', 'unit'));
            return false;
        }

        return true;
    }
}
