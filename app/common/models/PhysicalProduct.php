<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:34 م
 */

namespace Shop_products\Models;


use Phalcon\Validation;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Validators\UuidValidator;

class PhysicalProduct extends Product
{
    const WHITE_LIST = [
        'productBrandId',
        'productWeight',
        'productDimensions'
    ];

    /**
     *
     * @var string
     * @Column(column='product_type', type='string', nullable=false)
     */
    public $productType = ProductTypesEnums::TYPE_PHYSICAL;

    /**
     * @var string
     * @Column(column='product_brand_id', type='string', length=36, nullable=true)
     */
    public $productBrandId;

    /**
     * @var float
     * @Column(column='product_weight', type='float', nullable=true)
     */
    public $productWeight;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $productDimensions;

    /**
     * @return array
     */
    public static function getWhiteList()
    {
        return array_merge(parent::getWhiteList(), self::WHITE_LIST);
    }

    /**
     * @param null $columns
     * @return array
     */
    public function toArray($columns = null)
    {
        return array_merge(parent::toArray(),[
            'productBrandId' => $this->productBrandId,
            'productWeight' => $this->productWeight,
            'productDimensions' => $this->productDimensions
        ]);
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return array_merge(parent::toApiArray(), [
            'productBrandId' => $this->productBrandId,
            'productWeight' => $this->productWeight,
            'productDimensions' => $this->productDimensions
        ]);
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

        return !$messages->count() && parent::validation();
    }
}