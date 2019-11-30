<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:35 م
 */

namespace app\common\models;


use app\common\validators\rules\DownloadableProductRules;
use Phalcon\Validation;
use app\common\utils\DigitalUnitsConverterUtil;

/**
 * Class DownloadableProperties
 * @package app\common\models
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

    /**
     * @var DownloadableProductRules
     */
    private $validationRules;

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
    public function toApiArray(): array
    {
        return [
            'productDigitalSize' => (int) $this->productDigitalSize
        ];
    }

    /**
     * @return DownloadableProductRules
     */
    private function getValidationRules(): DownloadableProductRules
    {
        return $this->validationRules ?? $this->validationRules = new DownloadableProductRules();
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
                'max' => $this->getValidationRules()->maxDigitalSize,
                'messageMaximum' => 'Digital size exceeds the max limit ' . DigitalUnitsConverterUtil::bytesToMb(
                    $this->getValidationRules()->maxDigitalSize
                ),
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
