<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ١:٠٠ ص
 */

namespace app\common\models\embedded;


use app\common\models\BaseModel;
use app\common\models\Product;
use app\common\models\ProductImages;
use app\common\traits\ModelCollectionBehaviorTrait;
use app\common\validators\UuidValidator;
use Phalcon\Validation;

/**
 * Class Variation
 * @package app\common\models\embedded
 * @property ProductImages $image
 */
class Variation extends BaseModel
{
    use ModelCollectionBehaviorTrait;

    const WHITE_LIST = [
        'productId',
        'quantity',
        'userId',
        'imageId',
        'price',
        'salePrice'
    ];

    /**
     * @var string
     * @Column(column='variation_id', type='varchar', length=36, nullable=false)
     * @Primary
     */
    public $variationId;

    /**
     * @var string
     * @Column(column='product_id', type='varchar', length=36, nullable=false)
     */
    public $productId;

    /**
     * @var int
     * @Column(column='quantity', type='integer', length=11, nullable=false, default=0)
     */
    public $quantity = 0;

    /**
     * @var string
     * @Column(column='user_id', type='varchar', length=36, nullable=false)
     */
    public $userId;

    /**
     * @var string
     * @Column(column='image_id', type='varchar', length=36)
     */
    public $imageId;

    /**
     * @var float
     * @Column(column='price', type='float', nullable=false)
     */
    public $price;

    /**
     * @var float|null
     * @Column(column='sale_price', type='float', default=0)
     */
    public $salePrice = 0;

    /**
     * @var \DateTime
     * @Column(column='created_at', type='datetime', nullable=false)
     */
    public $createdAt;

    /**
     * @var \DateTime
     * @Column(column='updated_at', type='datetime', nullable=true)
     */
    public $updatedAt;

    /**
     * @var \DateTime
     * @Column(column='deleted_at', type='datetime', nullable=true)
     */
    public $deletedAt;

    /**
     * @var bool
     * @Column(column='is_deleted', type='boolean', nullable=false, default=false)
     */
    public $isDeleted = false;

    /**
     * @var VariationProperties
     */
    public $properties;

    /**
     * @return array
     */
    static public function getWhiteList(): array
    {
        return self::WHITE_LIST;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return 'product_variations';
    }

    /**
     * @throws \Exception
     */
    public function initialize()
    {
        $this->defaultBehavior();

        $this->belongsTo(
            'productId',
            Product::class,
            'productId',
            [
                'reusable' => true
            ]
        );

        $this->hasOne(
            'imageId',
            ProductImages::class,
            'imageId',
            [
                'alias' => 'image',
                'params' => [
                    'conditions' => 'isDeleted = false AND isVariationImage = true'
                ]
            ]
        );
    }

    public function beforeValidationOnCreate()
    {
        $this->variationId = $this->getDI()->getSecurity()->getRandom()->uuid();
    }

    public function beforeValidationOnUpdate()
    {
        $this->createdAt = $this->createdAt->format(self::$dateFormat);
        $this->updatedAt = $this->updatedAt ? $this->updatedAt->format(self::$dateFormat) : null;
    }

    public function beforeDelete()
    {
        $this->operationMode = self::OP_DELETE;
    }

    public function afterUpdate()
    {
        if ($this->operationMode == self::OP_DELETE) {
            if ($this->properties) {
                $this->properties->delete();
            }
            if ($this->image) {
                $this->image->delete();
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function afterFetch()
    {
        $this->createdAt = new \DateTime($this->createdAt);
        $this->updatedAt = $this->updatedAt ? new \DateTime($this->updatedAt) : null;
        $this->properties = VariationProperties::findFirst([
            'conditions' => [
                'variationId' => $this->variationId
            ]
        ]);
    }

    public function columnMap(): array
    {
        return [
            'variation_id' => 'variationId',
            'product_id' => 'productId',
            'quantity' => 'quantity',
            'user_id' => 'userId',
            'image_id' => 'imageId',
            'price' => 'price',
            'sale_price' => 'salePrice',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deleted_at',
            'is_deleted' => 'isDeleted'
        ];
    }

    /**
     * @param bool $assoc
     * @return array|VariationProperties
     */
    private function getProperties(bool $assoc = false)
    {
        if ($this->properties) {
            if ($assoc) {
                return $this->properties->toApiArray();
            } else {
                return $this->properties;
            }
        }
        return [];
    }

    /**
     * @param bool $assoc
     * @return ProductImages|array
     */
    private function getImage(bool $assoc = false)
    {
        if ($this->image) {
            if ($assoc) {
                return $this->image->toVariationImageArray();
            } else {
                return $this->image;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return [
            'variationId' => $this->variationId,
            'image' => $this->getImage(true),
            'properties' => $this->getProperties(true),
            'quantity' => (int) $this->quantity,
            'price' => (float) $this->price,
            'salePrice' => (float) $this->salePrice
        ];
    }

    /**
     * @return bool
     */
    public function validation(): bool
    {
        $validation = new Validation();

        $validation->add(
            ['productId', 'userId'],
            new UuidValidator([
                'allowEmpty' => false
            ])
        );

        $validation->add(
            'quantity',
            new Validation\Validator\NumericValidator([
                'allowFloat' => false,
                'allowSign' => false,
                'allowEmpty' => false
            ])
        );

        $this->_errorMessages = $validation->validate([
            'productId' => $this->productId,
            'userId' => $this->userId,
            'quantity' => $this->quantity
        ]);

        return !count($this->_errorMessages);
    }
}
