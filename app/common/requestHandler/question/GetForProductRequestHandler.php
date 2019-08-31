<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٢:٥٧ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\RulesAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Mvc\Controller;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class GetForProductRequestHandler extends RequestAbstract
{
    /** @var string */
    public $productId;

    public function __construct(Controller $controller, $productId = null)
    {
        $this->productId = $productId;
        parent::__construct($controller);
    }

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

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [];
    }
}
