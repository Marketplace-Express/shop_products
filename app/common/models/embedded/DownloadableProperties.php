<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:35 م
 */

namespace app\common\models\embedded;


use Phalcon\Validation;
use app\common\validators\rules\DownloadableProductRules;
use app\common\utils\DigitalUnitsConverterUtil;

/**
 * Class DownloadableProperties
 * @package app\common\models
 */
class DownloadableProperties extends Properties
{
    /** @var float */
    public $digitalSize;

    /**
     * @var DownloadableProductRules
     */
    private $validationRules;

    /**
     * @return array
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'digitalSize'
        ]);
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data): void
    {
        parent::setAttributes($data);
        $this->digitalSize = $data['digitalSize'];
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return array_merge(parent::toApiArray(), [
            'digitalSize' => DigitalUnitsConverterUtil::bytesToMb($this->digitalSize)
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
            'digitalSize',
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
            'digitalSize' => $this->digitalSize
        ]);

        $this->_errorMessages = $messages;

        return !$messages->count();
    }
}
