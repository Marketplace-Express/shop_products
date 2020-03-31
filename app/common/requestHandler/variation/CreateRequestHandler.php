<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ٥:٣١ م
 */

namespace app\common\requestHandler\variation;


use app\common\requestHandler\RequestAbstract;
use app\common\services\user\UserService;
use app\common\validators\rules\CommonVariationRules;
use app\common\validators\SkuValidator;
use app\common\validators\TypeValidator;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;

class CreateRequestHandler extends RequestAbstract
{
    /** @var int */
    public $quantity;

    /** @var float */
    public $price;

    /** @var float */
    public $salePrice;

    /** @var string */
    public $sku;

    /** @var string */
    public $imageId;

    /** @var array */
    public $attributes = [];

    /** @var CommonVariationRules */
    protected $validationRules;

    /**
     * CreateRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new CommonVariationRules());
    }

    /**
     * @return UserService
     */
    protected function getUserService(): UserService
    {
        return $this->di->getUserService();
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = new Validation();

        $validator->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'min' => $this->validationRules->minQuantity,
                'allowEmpty' => false
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

        // TODO: validate attributes from categories service

        return $validator->validate([
            'quantity' => $this->quantity,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'sku' => $this->sku
        ]);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'userId' => $this->getUserService()->userId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'imageId' => $this->imageId,
            'attributes' => $this->attributes,
            'sku' => $this->sku
        ];
    }
}
