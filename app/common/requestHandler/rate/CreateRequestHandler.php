<?php
/**
 * User: Wajdi Jurry
 * Date: ١١‏/٤‏/٢٠٢٠
 * Time: ٧:١٢ م
 */

namespace app\common\requestHandler\rate;


use app\common\requestHandler\IArrayData;
use app\common\requestHandler\RequestAbstract;
use app\common\services\user\UserService;
use app\common\validators\rules\RateRules;
use app\common\validators\UuidValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class CreateRequestHandler extends RequestAbstract implements IArrayData
{
    /** @var string */
    public $userId;

    /** @var string */
    public $productId;

    /** @var int */
    public $stars;

    /** @var string|null */
    public $text;

    /** @var array */
    public $imagesIds = [];

    /** @var RateRules */
    protected $validationRules;

    /**
     * CreateRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new RateRules());
    }

    /**
     * @return UserService
     */
    protected function getUserService(): UserService
    {
        return $this->di->getUserService();
    }

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

        $validator->add(
            'stars',
            new Validation\Validator\NumericValidator([
                'min' => $this->validationRules->minStars,
                'max' => $this->validationRules->maxStars
            ])
        );

        $validator->add(
            'text',
            new Validation\Validator\StringLength([
                'max' => $this->validationRules->maxTextLength,
                'allowEmpty' => $this->validationRules->allowEmptyText
            ])
        );

        $validator->add(
            'imagesIds',
            new Validation\Validator\Callback([
                'callback' => function ($data) {
                    if (count($data['imagesIds']) > $this->validationRules->maxNumOfImages) {
                        return false;
                    }
                    return true;
                },
                'message' => 'Maximum number of images exceeded'
            ])
        );

        return $validator->validate([
            'productId' => $this->productId,
            'stars' => $this->stars,
            'text' => $this->text,
            'imagesIds' => $this->imagesIds
        ]);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->getUserService()->userId,
            'productId' => $this->productId,
            'stars' => $this->stars,
            'text' => $this->text,
            'imagesIds' => $this->imagesIds
        ];
    }
}