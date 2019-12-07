<?php
/**
 * User: Wajdi Jurry
 * Date: ٦‏/١٢‏/٢٠١٩
 * Time: ١:١٠ م
 */

namespace app\common\requestHandler\product;


use app\common\enums\QuantityOperatorsEnum;
use app\common\requestHandler\RequestAbstract;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateQuantityRequestHandler extends RequestAbstract
{
    /** @var int */
    public $amount;

    /** @var string */
    public $operator;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();
        $validator->add(
            'amount',
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowSign' => false,
                'min' => 0
            ])
        );

        $validator->add(
            'operator',
            new Validation\Validator\InclusionIn([
                'domain' => QuantityOperatorsEnum::getValues()
            ])
        );

        return $validator->validate([
            'amount' => $this->amount,
            'operator' => $this->operator
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'operator' => $this->operator
        ];
    }
}
