<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 07:40 م
 */

namespace app\common\requestHandler\product;


use app\common\requestHandler\RequestAbstract;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\validators\UuidValidator;

class DeleteRequestHandler extends RequestAbstract
{
    public $vendorId;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();
        $validator->add(
            'vendorId',
            new UuidValidator()
        );
        return $validator->validate(['vendorId' => $this->vendorId]);
    }
}
