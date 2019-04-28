<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:34 م
 */

namespace Shop_products\Models;


use Phalcon\Validation;
use Shop_products\Validators\UuidValidator;

/**
 * Class PhysicalProduct
 * @package Shop_products\Models
 * @Entity
 */
class PhysicalProduct extends BaseModel
{
    const WHITE_LIST = [
        'productBrandId',
        'productWeight',
        'packageDimensions'
    ];

    /**
     * @var string
     * @PrimaryKey
     * @Column(column='product_id', type="string", length=36)
     */
    public $productId;

    /**
     * @var string
     * @Column(column='product_brand_id', type='string', length=36, nullable=true)
     */
    public $productBrandId;

    /**
     * @var float
     * @Column(column='product_weight', type='float')
     */
    public $productWeight;

    /**
     * @return string
     */
    public function getSource()
    {
        return 'physical_products';
    }

    public function initialize()
    {
        $this->belongsTo(
            'productId',
            Product::class,
            'productId',
            [
                'reusable' => true
            ]
        );
    }

    /**
     * @return array
     */
    public static function getWhiteList()
    {
        return array_merge(Product::getWhiteList(), self::WHITE_LIST);
    }

    public function columnMap()
    {
        return [
            'product_id' => 'productId',
            'product_weight' => 'productWeight',
            'product_brand_id' => 'productBrandId'
        ];
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return [
            'productBrandId' => $this->productBrandId,
            'productWeight' => (float) $this->productWeight
        ];
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            'productBrandId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'productWeight',
            new Validation\Validator\NumericValidator([
                'allowFloat' => true,
                'min' => 0,
                'allowEmpty' => true,
                'message' => 'Invalid weight'
            ])
        );

        $messages = $validation->validate([
            'productBrandId' => $this->productBrandId,
            'productWeight' => $this->productWeight
        ]);

        $this->_errorMessages = $messages;

        return !$messages->count();
    }
}