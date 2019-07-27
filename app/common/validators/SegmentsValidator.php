<?php
/**
 * User: Wajdi Jurry
 * Date: 25/01/19
 * Time: 03:55 م
 */

namespace app\common\validators;


use Phalcon\Validation;
use Phalcon\Validation\Message;
use Phalcon\Validation\Validator;
use Phalcon\Validation\ValidatorInterface;
use app\common\enums\AgeRangeEnum;
use app\common\enums\CountriesEnum;
use app\common\enums\GenderEnum;

class SegmentsValidator extends Validator implements ValidatorInterface
{

    /**
     * Executes the validation
     *
     * @param \Phalcon\Validation $validation
     * @param string $attribute
     * @return bool
     */
    public function validate(Validation $validation, $attribute)
    {
        $segments = $validation->getValue($attribute);

        if (!property_exists($segments, 'countries')
            || !property_exists($segments, 'age')
            || !property_exists($segments, 'gender')
        ) {
            $validation->appendMessage(
                new Message($this->getOption('message') ?? 'Invalid segments provided',
                    $attribute)
            );
            return false;
        }

        $validator = new Validation();

        $validator->add(
            ['countries', 'age', 'gender'],
            new TypeValidator([
                'type' => TypeValidator::TYPE_ARRAY,
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'countries',
            new ArrayInclusionInValidator([
                'domain' => CountriesEnum::getKeys(),
                'message' => 'Invalid country provided in segment'
            ])
        );

        $validator->add(
            'age',
            new ArrayInclusionInValidator([
                'domain' => AgeRangeEnum::getKeys(),
                'message' => 'Invalid age provided in segment'
            ])
        );

        $validator->add(
            'gender',
            new ArrayInclusionInValidator([
                'domain' => GenderEnum::getValues(),
                'message' => 'Invalid gender provided in segment'
            ])
        );

        $messages = $validator->validate([
            'countries' => $segments->countries,
            'age' => $segments->age,
            'gender' => $segments->gender
        ]);

        if (count($messages)) {
            foreach ($messages as $message) {
                $validation->appendMessage($message);
            }
            return false;
        }

        return true;
    }
}