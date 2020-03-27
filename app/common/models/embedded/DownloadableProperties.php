<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:35 م
 */

namespace app\common\models\embedded;


use app\common\validators\rules\DownloadableProductRules;
use Phalcon\Validation;
use app\common\utils\DigitalUnitsConverterUtil;

/**
 * Class DownloadableProperties
 * @package app\common\models
 */
class DownloadableProperties extends Properties
{
    /** @var float */
    public $productDigitalSize;

    /**
     * @var DownloadableProductRules
     */
    private $validationRules;

    /**
     * @param array $data
     */
    public function setAttributes(array $data): void
    {
        parent::setAttributes($data);
        $this->productDigitalSize = $data['productDigitalSize'];
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return array_merge(parent::toApiArray(), [
            'productDigitalSize' => DigitalUnitsConverterUtil::bytesToMb($this->productDigitalSize)
        ]);
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
