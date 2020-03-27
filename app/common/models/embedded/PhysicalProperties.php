<?php
/**
 * User: Wajdi Jurry
 * Date: 08/03/19
 * Time: 10:34 م
 */

namespace app\common\models\embedded;


use app\common\validators\PackageDimensionsValidator;
use app\common\validators\WeightValidator;
use app\common\models\embedded\physical\{
    Dimensions,
    Weight
};
use Phalcon\Validation;

/**
 * Class PhysicalProperties
 * @package app\common\models
 * @Entity
 */
class PhysicalProperties extends Properties
{
    /** @var Dimensions */
    public $packageDimensions;

    /** @var Weight */
    public $productWeight;

    public function initialize()
    {
        parent::initialize();
        $this->packageDimensions = new Dimensions();
        $this->productWeight = new Weight();
    }

    /**
     * @param array $data
     */
    public function setAttributes(array $data)
    {
        parent::setAttributes($data);
        if (!empty($data['productPackageDimensions'])) {
            $this->packageDimensions = $data['productPackageDimensions'];
        }
        if (!empty($data['productWeight'])) {
            $this->productWeight = $data['productWeight'];
        }
    }

    /**
     * @return array
     */
    public function toApiArray(): array
    {
        return array_merge(parent::toApiArray(), [
            'packageDimensions' => $this->packageDimensions->toApiArray(),
            'productWeight' => $this->productWeight->toApiArray()
        ]);
    }

    /**
     * @return bool
     */
    public function validation()
    {
        $validation = new Validation();

        $validation->add(
            'productWeight',
            new WeightValidator([
                'allowEmpty' => true
            ])
        );

        $validation->add(
            'packageDimensions',
            new PackageDimensionsValidator([
                'allowEmpty' => true
            ])
        );

        $this->_errorMessages = $validation->validate([
            'productWeight' => $this->productWeight,
            'packageDimensions' => $this->packageDimensions
        ]);

        return !$this->_errorMessages->count();
    }
}
