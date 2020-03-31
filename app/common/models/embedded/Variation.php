<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ١:٠٠ ص
 */

namespace app\common\models\embedded;


use app\common\validators\SkuValidator;
use app\common\models\{
    BaseModel,
    Product,
    ProductImages
};
use app\common\validators\UuidValidator;
use Phalcon\Mvc\Model\ResultsetInterface;
use Phalcon\Validation;

/**
 * Class Variation
 * @package app\common\models\embedded
 * @property ProductImages $image
 */
class Variation extends BaseModel
{
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
     * @Column(column='image_id', type='varchar', length=36, nullable=true)
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
     * @var string
     * @Column(column='sku', type='string', nullable=false)
     */
    public $sku;

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
     * @var string
     * @Column(column='deletion_token', type='string', length=36, default='N/A')
     */
    public $deletionToken = 'N/A';

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

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return self[]
     */
    static public function find($parameters = null)
    {
        $operator = '';
        if (!array_key_exists('conditions', $parameters)) {
            $parameters['conditions'] = '';
        }
        if (!empty($parameters['conditions'])) {
            $operator = ' AND ';
        }
        $parameters['conditions'] .= $operator.'isDeleted = false';
        return parent::find($parameters);
    }

    /**
     * @param null $parameters
     * @return \Phalcon\Mvc\Model|Variation
     */
    public static function findFirst($parameters = null)
    {
        /** @var ResultsetInterface $models */
        $models = self::find($parameters);
        return $models->getFirst();
    }

    public function beforeValidationOnCreate()
    {
        $this->variationId = $this->getDI()->getSecurity()->getRandom()->uuid();
    }

    public function beforeDelete()
    {
        $this->operationMode = self::OP_DELETE;
        $this->deletionToken = $this->getDI()->getSecurity()->getRandom()->base58();
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
        $this->properties = VariationProperties::findFirst([
            'conditions' => [
                'variationId' => $this->variationId
            ]
        ]);
    }

    /**
     * @return array
     */
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
            'sku' => 'sku',
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
            'salePrice' => (float) $this->salePrice,
            'sku' => $this->sku
        ];
    }

    /**
     * @return bool
     */
    public function validation(): bool
    {
        $validation = new Validation();
        $validation->bind($this, $this->toArray());

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

        $validation->add(
            'price',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'allowFloat' => true
            ])
        );

        $validation->add(
            'salePrice',
            new Validation\Validator\NumericValidator([
                'min' => 0,
                'allowFloat' => true,
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'sku',
            new SkuValidator()
        );

        if ($this->operationMode != self::OP_DELETE) {
            $validation->add(
                ['productId', 'sku', 'isDeleted'],
                new Validation\Validator\Uniqueness([
                    'message' => 'SKU should be unique per variation'
                ])
            );
        }

        $this->_errorMessages = $validation->validate([
            'productId' => $this->productId,
            'userId' => $this->userId,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'salePrice' => $this->salePrice,
            'sku' => $this->sku
        ]);

        return !count($this->_errorMessages);
    }
}
