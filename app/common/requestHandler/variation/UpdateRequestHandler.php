<?php


namespace app\common\requestHandler\variation;


use app\common\requestHandler\RequestAbstract;
use app\common\validators\rules\CommonVariationRules;
use app\common\validators\SkuValidator;
use app\common\validators\TypeValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class UpdateRequestHandler extends RequestAbstract
{
    /** @var int */
    public $quantity;

    /** @var float */
    public $price;

    /** @var float */
    public $salePrice;

    /** @var string */
    public $sku;

    /** @var string|null */
    public $imageId;

    /** @var array */
    public $attributes = [];

    /** @var CommonVariationRules */
    protected $validationRules;

    /**
     * UpdateRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new CommonVariationRules());
    }

    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            ['quantity', 'price'],
            new Validation\Validator\PresenceOf([
                'allowEmpty' => false
            ])
        );

        $validator->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'min' => $this->validationRules->minQuantity,
            ])
        );

        $validator->add(
            'price',
            new Validation\Validator\NumericValidator([
                'min' => 0,
                'allowFloat' => true
            ])
        );

        $validator->add(
            'salePrice',
            new Validation\Validator\NumericValidator([
                'min' => 0,
                'allowFloat' => true,
                'allowEmpty' => true
            ])
        );

        $validator->add(
            ['price', 'salePrice'],
            new TypeValidator([
                'type' => TypeValidator::TYPE_FLOAT
            ])
        );

        $validator->add(
            'sku',
            new SkuValidator()
        );

        $validator->add(
            'attributes',
            new TypeValidator([
                'type' => TypeValidator::TYPE_ARRAY
            ])
        );

        $validator->add(
            'imageId',
            new TypeValidator([
                'type' => TypeValidator::TYPE_STRING
            ])
        );

        return $validator->validate([
            'quantity' => $this->quantity,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'sku' => $this->sku,
            'imageId' => $this->imageId,
            'attributes' => $this->attributes,
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (empty($this->imageId)) {
            $this->imageId = null;
        }

        return [
            'quantity' => $this->quantity,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'imageId' => $this->imageId,
            'attributes' => $this->attributes,
            'sku' => $this->sku,
        ];
    }
}