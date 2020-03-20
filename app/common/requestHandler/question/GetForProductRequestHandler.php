<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٢:٥٧ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class GetForProductRequestHandler extends RequestAbstract
{
    /** @var string */
    public $productId;

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
        return $validator->validate([
            'productId' => $this->productId
        ]);
    }
}
