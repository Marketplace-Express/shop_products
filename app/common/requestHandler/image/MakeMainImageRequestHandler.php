<?php
/**
 * User: Wajdi Jurry
 * Date: 3/30/20
 * Time: 7:23 PM
 */

namespace app\common\requestHandler\image;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class MakeMainImageRequestHandler extends RequestAbstract
{
    /** @var string */
    public $productId;

    /**
     * @inheritDoc
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'productId',
            new UuidValidator()
        );

        return $validator->validate([
            'productId' => $this->productId
        ]);
    }
}