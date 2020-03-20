<?php
/**
 * User: Wajdi Jurry
 * Date: 13/01/19
 * Time: 02:13 م
 */

namespace app\common\requestHandler\product;


use app\common\validators\PackageDimensionsValidator;
use app\common\validators\rules\PhysicalProductRules;
use app\common\validators\WeightValidator;
use Phalcon\Mvc\Controller;
use Phalcon\Validation\Message\Group;
use app\common\enums\ProductTypesEnum;
use app\common\validators\UuidValidator;

class CreatePhysicalProductRequestHandler extends AbstractCreateRequestHandler
{
    /**
     * @var string
     */
    public $brandId;

    /**
     * @var \app\common\models\embedded\physical\Weight
     */
    public $weight;

    /**
     * @var \app\common\models\embedded\physical\PackageDimensions
     */
    public $packageDimensions;

    /**
     * @var PhysicalProductRules
     */
    protected $validationRules;

    /**
     * CreatePhysicalProductRequestHandler constructor.
     */
    public function __construct()
    {
        parent::__construct(new PhysicalProductRules());
    }

    /**
     * @return array
     */
    protected function fields()
    {
        return array_merge(parent::fields(), [
            'brandId'  => $this->brandId,
            'weight' => $this->weight,
            'packageDimensions' => $this->packageDimensions
        ]);
    }

    /** Validate request fields using \Phalcon\Validation\Validator
     * @return Group
     */
    public function validate(): Group
    {
        $validator = parent::mainValidator();

        $validator->add(
            'brandId',
            new UuidValidator([
                'allowEmpty' => true
            ])
        );

        $validator->add(
            'weight',
            new WeightValidator()
        );

        $validator->add(
            'packageDimensions',
            new PackageDimensionsValidator()
        );

        return $validator->validate($this->fields());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'productBrandId' => $this->brandId,
            'productType' => ProductTypesEnum::TYPE_PHYSICAL,
            'productWeight' => $this->weight,
            'productPackageDimensions' => $this->packageDimensions
        ]);
    }
}
