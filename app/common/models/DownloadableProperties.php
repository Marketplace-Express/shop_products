<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:35 م
 */

namespace Shop_products\Models;


use Phalcon\Validation;
use Shop_products\Utils\DigitalUnitsConverterUtil;

/**
 * Class DownloadableProperties
 * @package Shop_products\Models
 */
class DownloadableProperties extends BaseModel
{
    const WHITE_LIST = [
        'productDigitalSize'
    ];

    const MODEL_ALIAS = 'dp';

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
        return 'downloadable_properties';
    }

    public function columnMap()
    {
        return [
            'product_id' => 'productId',
            'product_digital_size' => 'productDigitalSize'
        ];
    }

    public static function count($parameters = null)
    {
        return count(array_filter([
            self::model()->productDigitalSize
        ]));
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