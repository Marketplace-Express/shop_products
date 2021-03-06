<?php
/**
 * User: Wajdi Jurry
 * Date: 27/07/19
 * Time: 02:00 م
 */

namespace app\common\requestHandler\image;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class DeleteRequestHandler extends RequestAbstract
{
    /** @var string */
    public $albumId;

    /** @var string */
    public $productId;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'albumId',
            new Validation\Validator\PresenceOf()
        );

        $validator->add(
            'productId',
            new UuidValidator()
        );

        return $validator->validate([
            'albumId' => $this->albumId,
            'productId' => $this->productId
        ]);
    }
}
