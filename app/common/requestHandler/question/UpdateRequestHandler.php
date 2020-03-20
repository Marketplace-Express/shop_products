<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٣:٢٢ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\QuestionRules;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateRequestHandler extends RequestAbstract
{
    /** @var string */
    public $text;

    /** @var QuestionRules */
    protected $validationRules;

    /**
     * UpdateRequestHandler constructor.
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
        return $validator->validate([
            'text' => $this->text
        ]);
    }
}
