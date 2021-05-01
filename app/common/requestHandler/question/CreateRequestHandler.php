<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٧‏/٧‏/٢٠١٩
 * Time: ٨:١٣ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\QuestionRules;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class CreateRequestHandler extends RequestAbstract
{
    /** @var string */
    public $text;

    /** @var string */
    public $productId;

    /** @var string */
    public $userId;

    /** @var QuestionRules */
    protected $validationRules;

    /**
     * CreateRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new QuestionRules());
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'text',
            new Validation\Validator\StringLength([
                'min' => $this->validationRules->minTextLength,
                'max' => $this->validationRules->maxTextLength
            ])
        );

        $validator->add(
            ['productId', 'userId'],
            new UuidValidator()
        );

        return $validator->validate([
            'text' => $this->text,
            'productId' => $this->productId,
            'userId' => $this->userId,
        ]);
    }
}
