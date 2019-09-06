<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:34 م
 */

namespace app\common\models;


use app\common\enums\WeightUnitsEnum;
use Phalcon\Validation;
use app\common\validators\UuidValidator;

/**
 * Class PhysicalProperties
 * @package app\common\models
 * @Entity
 */
class PhysicalProperties extends BaseModel
{
    const WHITE_LIST = [
        'productBrandId',
        'productWeight',
        'productWeightUnit',
        'packageDimensions'
    ];

    const MODEL_ALIAS = 'pp';

    /**
     * @var int
     * @Primary
     * @Identity
     * @Column(column='row_id', type="int", length=11)
     */
    public $rowId;

    /**
     * @var string
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
     * @var string
     * @Column(column='product_weight_unit', type='string', length=6, nullable=false)
     */
    public $productWeightUnit;

    /**
     * @return string
     */
    public function getSource()
    {
        return 'physical_properties';
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
            'productWeight' => [
                'amount' => $this->productWeight,
                'unit' => $this->productWeightUnit
            ]
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

        $validation->add(
            'productWeightUnit',
            new Validation\Validator\InclusionIn([
                'domain' => WeightUnitsEnum::getAll()
            ])
        );

        $this->_errorMessages = $validation->validate([
            'productBrandId' => $this->productBrandId,
            'productWeight' => $this->productWeight,
            'productWeightUnit' => $this->productWeightUnit
        ]);

        return !$this->_errorMessages->count();
    }
}
