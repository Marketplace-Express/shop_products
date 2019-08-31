<?php
/**
 * User: Wajdi Jurry
 * Date: ٢٤‏/٨‏/٢٠١٩
 * Time: ٣:٢٢ م
 */

namespace app\common\requestHandler\question;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\QuestionRules;
use app\common\validators\rules\RulesAbstract;
use app\common\validators\UuidValidator;
use Phalcon\Mvc\Controller;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateRequestHandler extends RequestAbstract
{
    /** @var string */
    public $id;

    /** @var string */
    public $text;

    /** @var QuestionRules */
    protected $validationRules;

    public function __construct(Controller $controller, $id = null)
    {
        $this->id = $id;
        parent::__construct($controller, new QuestionRules());
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
        $validator->add(
            'text',
            new Validation\Validator\StringLength([
                'min' => $this->validationRules->minTextLength,
                'max' => $this->validationRules->maxTextLength
            ])
        );
        return $validator->validate([
            'id' => $this->id,
            'text' => $this->text
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
