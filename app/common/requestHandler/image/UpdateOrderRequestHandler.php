<?php
/**
 * User: Wajdi Jurry
 * Date: ٦‏/٩‏/٢٠١٩
 * Time: ٥:٢٢ م
 */

namespace app\common\requestHandler\image;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateOrderRequestHandler extends RequestAbstract
{
    /** @var string */
    public $productId;

    /** @var int */
    public $order;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'productId',
            new UuidValidator()
        );

        $validator->add(
            'order',
            new Validation\Validator\NumericValidator([
                'min' => 0
            ])
        );

        return $validator->validate([
            'productId' => $this->productId,
            'order' => $this->order
        ]);
    }
}
