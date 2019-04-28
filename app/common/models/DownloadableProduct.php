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

/**
 * Class DownloadableProduct
 * @package Shop_products\Models
 */
class DownloadableProduct extends BaseModel
{
    const WHITE_LIST = [
        'productDigitalSize'
    ];

    /**
     * @var string
     * @PrimaryKey
     * @Column(column='product_id', type="string", length=36)
     */
    public $productId;

    /**
     * @var float
     * @Column(column='product_digital_size', type='integer', length=11, nullable=false)
     */
    public $productDigitalSize;

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
     * @return string
     */
    public function getSource()
    {
        return 'downloadable_products';
    }

    public static function getWhiteList()
    {
        return array_merge(Product::getWhiteList(), self::WHITE_LIST);
    }

    public function columnMap()
    {
        return [
            'product_id' => 'productId',
            'product_digital_size' => 'productDigitalSize'
        ];
    }

    /**
     * @return array
     */
    public function toApiArray()
    {
        return [
            'productDigitalSize' => (int) $this->productDigitalSize
        ];
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

        return !$messages->count();
    }
}