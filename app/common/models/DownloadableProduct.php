<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:35 م
 */

namespace Shop_products\Models;


use Phalcon\Validation;
use Shop_products\Enums\ProductTypesEnums;
use Shop_products\Utils\DigitalUnitsConverterUtil;

class DownloadableProduct extends Product
{
    const DOWNLOADABLE_PRODUCT_WHITE_LIST = [
        'productDigitalSize'
    ];

    /**
     * @var string
     */
    public $productType = ProductTypesEnums::TYPE_DOWNLOADABLE;

    /**
     * @var float
     */
    public $productDigitalSize;

    public static function getWhiteList()
    {
        return array_merge(parent::getWhiteList(), self::DOWNLOADABLE_PRODUCT_WHITE_LIST);
    }

    /**
     * @param null $columns
     * @return array
     */
    public function toArray($columns = null)
    {
        return array_merge(parent::toArray($columns), [
            'productType' => $this->productType,
            'productDigitalSize' => $this->productDigitalSize
        ]);
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return array_merge(parent::toApiArray(), [
            'productDigitalSize' => $this->productDigitalSize
        ]);
    }

    /**
     * @return mixed
     */
    private function getMaxDigitalSizeValidationConfig()
    {
        return $this->getDI()->getConfig()->application->validation->downloadable->maxDigitalSize;
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            'productDigitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->getMaxDigitalSizeValidationConfig(),
                'messageMaximum' => 'Digital size exceeds the max limit ' . DigitalUnitsConverterUtil::bytesToMb($this->getMaxDigitalSizeValidationConfig()),
                'messageMinimum' => 'Invalid digital size'
            ])
        );

        $messages = $validation->validate([
            'productDigitalSize' => $this->productDigitalSize
        ]);

        $this->_errorMessages = $messages;

        return !$messages->count() && parent::validation();
    }
}