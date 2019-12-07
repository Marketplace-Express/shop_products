<?php
/**
 * User: Wajdi Jurry
 * Date: ٧‏/١٢‏/٢٠١٩
 * Time: ١:٠٠ ص
 */

namespace app\common\models\embedded;


use app\common\models\BaseModel;
use app\common\models\Product;
use app\common\traits\ModelCollectionBehaviorTrait;

/**
 * Class Variation
 * @package app\common\models\embedded
 */
class Variation extends BaseModel
{
    use ModelCollectionBehaviorTrait;

    const WHITE_LIST = [
        'productId',
        'quantity',
        'userId'
    ];

    /**
     * @var string
     * @Column(column='variation_id', type='varchar', length=36, nullable=false)
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
    }

    public function beforeCreate()
    {
        $this->variationId = $this->getDI()->getSecurity()->getRandom()->uuid();
    }

    public function afterFetch()
    {
        $this->createdAt = new \DateTime($this->createdAt);
        $this->updatedAt = $this->updatedAt ? new \DateTime($this->updatedAt) : null;
    }

    public function columnMap(): array
    {
        return [
            'variation_id' => 'variationId',
            'product_id' => 'productId',
            'quantity' => 'quantity',
            'user_id' => 'userId',
            'created_at' => 'createdAt',
            'updated_at' => 'updatedAt',
            'deleted_at' => 'deleted_at',
            'is_deleted' => 'isDeleted'
        ];
    }

    public function toApiArray(): array
    {
        return [
            'variationId' => $this->variationId
        ];
    }
}
