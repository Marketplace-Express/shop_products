<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٣:٤٥ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class DeleteRequestHandler extends RequestAbstract
{
    /** @var string */
    public $id;

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();
        $validator->add(
            'id',
            new UuidValidator()
        );
        return $validator->validate(['id' => $this->id]);
    }
}
