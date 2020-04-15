<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace app\common\requestHandler\product;


use app\common\validators\rules\DownloadableProductRules;
use Phalcon\Validation;
use Phalcon\Validation\Message\Group;
use app\common\enums\ProductTypesEnum;
use app\common\utils\DigitalUnitsConverterUtil;

class CreateDownloadableProductRequestHandler extends AbstractCreateRequestHandler
{
    /** @var int */
    public $digitalSize;

    /**
     * @var DownloadableProductRules
     */
    protected $validationRules;

    /**
     * CreateDownloadableProductRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new DownloadableProductRules());
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'digitalSize' => $this->digitalSize
        ]);
    }

    /** Validate request fields using \Phalcon\Validation
     * @return Group
     */
    public function validate(): Group
    {
        $validator = $this->mainValidator();

        $validator->add(
            'digitalSize',
            new Validation\Validator\PresenceOf([
                'message' => 'You have to provide digital size'
            ])
        );

        $validator->add(
            'digitalSize',
            new Validation\Validator\NumericValidator([
                'min' => 1,
                'max' => $this->validationRules->maxDigitalSize,
                'messageMaximum' => 'Digital size exceeds the max limit ' .
                    DigitalUnitsConverterUtil::bytesToMb($this->validationRules->maxDigitalSize).
                    ' Mb',
                'messageMinimum' => 'Invalid digital size'
            ])
        );

        return $validator->validate($this->fields());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'productType' => ProductTypesEnum::TYPE_DOWNLOADABLE,
            'digitalSize' => $this->digitalSize
        ]);
    }
}
