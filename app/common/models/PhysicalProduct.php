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
    const PHYSICAL_PRODUCT_WHITE_LIST = [
        'productBrandId',
        'productWeight',
        'productDimensions'
    ];

    /**
     *
     * @var string
     */
    public $productType = ProductTypesEnums::TYPE_PHYSICAL;

    /**
     *
     * @var string
     */
    public $productBrandId;

    /**
     * @var float
     */
    public $productWeight;

    /**
     * @var array
     * This value appended from Mongo Collection
     */
    private $dimensions;

    /**
     * @param array $dimensions
     */
    public function setDimensions(array $dimensions): void
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return array
     */
    public static function getWhiteList()
    {
        return array_merge(parent::getWhiteList(), self::PHYSICAL_PRODUCT_WHITE_LIST);
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
            'productDimensions' => $this->dimensions
        ]);
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return array_merge(parent::toApiArray(), [
            'productBrandId' => $this->productBrandId,
            'productType' => $this->productType,
            'productWeight' => $this->productWeight,
            'productDimensions' => $this->dimensions
        ]);
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            ['productBrandId'],
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