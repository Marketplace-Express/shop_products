<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٣:٥٢ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\RulesAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Mvc\Controller;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class GetByIdRequestHandler extends RequestAbstract
{
    /** @var string */
    public $id;

    public function __construct(Controller $controller, $id)
    {
        $this->id = $id;
        parent::__construct($controller);
    }

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

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
